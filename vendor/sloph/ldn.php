<?
require_once('../init.php');

/*** LDN ***/

function on_get($ep, $ct=null){
  
  if($ct === null){
    $headers = apache_request_headers();
    $ct = $headers["Accept"];
  }
  $acceptheaders = new AcceptHeader($ct);
  $contains = get_container_dynamic($ep, "https://rhiaro.co.uk/incoming/", "query_select_o_where", array(array("https://rhiaro.co.uk/incoming/" => "ldp:contains"), "https://rhiaro.co.uk/incoming/moderation"), $ct);
  // var_dump($contains);
  $result = conneg($acceptheaders, $contains);
  
  return $result;
}

function on_post($ep, $data){
  // Generate notification URI
  $uri = "https://rhiaro.co.uk".str_replace(".", "", uniqid("/n/", true));
  $notification = new EasyRdf_Graph();
  $notification->parse($data, 'jsonld', $uri);
  $ar = $notification->toRdfPhp();
  $updated = new EasyRdf_Graph($uri);
  // If notification contains bnode subjects, replace with the graph uri
  $subject_uris = get_subject_uris($notification);
  foreach($subject_uris as $s){
    $r = $notification->resource($s);
    if($r->isBNode()){
      $updated->parse(array($uri => $ar[$s]), 'php', $uri);
    }else{
      $updated->parse(array($s => $ar[$s]), 'php', $s);
    }
  }
  // Insert all sent triples into notification graph
  //  $uri { s p o }
  $modg = "https://rhiaro.co.uk/incoming/moderation";
  $inbox = "https://rhiaro.co.uk/incoming/";
  $triples = $updated->serialise('ntriples');
  $q1 = query_insert($triples, $uri);  
  $res1 = execute_query($ep, $q1);
  if($res1){
    // Insert triples in /moderation graph, so I can do per-triple acl in future
    $q2 = query_insert($triples, $modg);  
    $res2 = execute_query($ep, $q2);
    if($res2){
      // Insert notification into moderation graph and container/collection
      //  /moderation { /incoming/ as:items $uri }
      //  /moderation { /incoming/ ldp:contains $uri }
      $triples2 = "
  <$inbox> <http://www.w3.org/ns/ldp#contains> <$uri> .
  <$inbox> <https://www.w3.org/ns/activitystreams#items> <$uri> . ";
      $q3 = query_insert($triples2, $modg);
      $res3 = execute_query($ep, $q3);
      if($res3){
        // Return Location
        return $uri;
      }else{
        echo "Query 3 failed";
      }
    }else{
      echo "Query 2 failed";
    }
  }else{
    echo "Query 1 failed";
  }
  return false;
}

/*** Validating input ***/

function supported_content_type($ct){
  $supported = false;
  $sent = new AcceptHeader($ct);
  $cts = array("application/ld+json", "application/activity+json", "text/html");
  if(in_array($sent[0]["raw"], $cts)){
    $supported = true;
  }
  return $supported;
}

function valid_data($data){
  $valid = false;
  $parsed = json_decode($data, true);
  // Reject anything that isn't JSON for now
  if($parsed !== null){
    $new = new EasyRdf_Graph();
    try{
      $new->parse($data, 'jsonld');
      $valid = true;
    }catch(EasyRdf_Parser_Exception $e){
      // TODO: try other syntaxes one day
    }
  }
  return $valid;
}

function verify_data($data){
  // If it came from webmention.io, it's a valid webmention.
  // If it contains a URL I can parse as RDF and it contains a link to me, it's valid.
  // If it's something I subscribed to, it's valid.
  // If it contains an authorization header I recognise, it's valid.
  // TODO: Add other verification rules.
}

/*** Other stuff ***/

function this_form($post){
  $json = array("@context" => "https://www.w3.org/ns/activitystreams"
              , "generator" => "https://rhiaro.co.uk/sloph"
              , "published" => date(DATE_ATOM)
              , "content" => "");
  $json['content'] = $post['content'];
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);
  return $json;
}

function webmentionio($json){
  // TODO
  // Use webmention.io webhook to LDN these up
  // {
  //   "secret": "1234abcd",
  //   "source": "http://rhiaro.co.uk/2015/11/1446953889",
  //   "target": "http://aaronparecki.com/notes/2015/11/07/4/indiewebcamp",
  //   "post": {
  //     "type": "entry",
  //     "author": {
  //       "name": "Amy Guy",
  //       "photo": "http://webmention.io/avatar/rhiaro.co.uk/829d3f6e7083d7ee8bd7b20363da84d88ce5b4ce094f78fd1b27d8d3dc42560e.png",
  //       "url": "http://rhiaro.co.uk/about#me"
  //     },
  //     "url": "http://rhiaro.co.uk/2015/11/1446953889",
  //     "published": "2015-11-08T03:38:09+00:00",
  //     "name": "repost of http://aaronparecki.com/notes/2015/11/07/4/indiewebcamp",
  //     "repost-of": "http://aaronparecki.com/notes/2015/11/07/4/indiewebcamp",
  //     "wm-property": "repost-of"
  //   }
  // }
  return $json;
}

/*** And... action ***/

header("Accept-Post: application/ld+json");

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  $body = file_get_contents('php://input');
  $headers = apache_request_headers();
  $ct = $headers["Content-Type"];

  if(isset($_POST) && !empty($_POST['content'])){
  
    if(isset($_POST['notawhat']) && strtolower($_POST['notawhat']) == "i am not a robot"){
      $data = this_form($_POST);
      $was_form = true;
      $robot = false;
    }else{
      $data = array();
      $was_form = true;
      $robot = true;
    }

  }elseif(isset($body) && !empty($body)){

    $data = $body;
    $was_form = false; $robot = false;

    if(!supported_content_type($ct)){
      header("HTTP/1.1 415 Unsupported Media Type");
      echo "Try again with JSON-LD\n";
      die();
    }
    if(!valid_data($body)){
      header("HTTP/1.1 400 Bad Request");
      echo "This is not valid JSON-LD\n";
      die();
    }

  }else{
    header("HTTP/1.1 400 Bad Request");
    echo "No request body :(\n";
    die();
  }

  if(!$robot){
    $uri = on_post($ep, $data);
    $sent = true;
  }
  
  if(!$was_form){
    if($uri){
        header("HTTP/1.1 201 Created");
        header("Location: $uri");
    }else{
      header("HTTP/1.1 500 Internal Server Error");
      echo "Everything you sent was fine but I failed to store notification, sorry :( Try again?";
      die();
    }
  }else{
    $content = on_get($ep, "text/html");
    $content = $content['content'];
    $contains = $content->toRdfPhp();
    include '../../views/incoming.php';
  }

}elseif($_SERVER['REQUEST_METHOD'] === 'GET'){
  
  $result = on_get($ep);
  $header = $result['header'];
  $content = $result['content'];
  if(gettype($content) == "string"){
    
    header($header);
    echo $content;
  
  }else{
    $contains = $content->toRdfPhp();
    include '../../views/incoming.php';
  }
}

?>