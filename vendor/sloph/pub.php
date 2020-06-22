<?
require_once('../init.php');

function on_get($ep, $guest=false, $ct=null){

  if($guest){
    $graph = "https://blog.rhiaro.co.uk/guest/";
  }else{
    $graph = "https://blog.rhiaro.co.uk/";
  }

  if($ct === null){
    $headers = apache_request_headers();
    $ct = $headers["Accept"];
  }
  $acceptheaders = new AcceptHeader($ct);
  $q = query_construct_outbox($graph);
  $res = execute_query($ep, $q);
  $graph = new EasyRdf_Graph("https://rhiaro.co.uk/outgoing/");
  // var_dump($res);
  $graph->parse($res, 'php');
  // echo $graph->dump();
  $result = conneg($acceptheaders, $graph);

  return $result;

}

function verify_token($token){
  $ch = curl_init("https://tokens.indieauth.com/token");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
       "Content-Type: application/x-www-form-urlencoded"
      ,"Authorization: $token"
  ));
  $response = Array();
  parse_str(curl_exec($ch), $response);
  curl_close($ch);
  return $response;
}

function graph_route($me, $incoming){
  if($me != "https://rhiaro.co.uk/#me"){
    $graph = "https://blog.rhiaro.co.uk/guest/";
  }else{
    $graph = "https://blog.rhiaro.co.uk/";
  }

  // TODO: this can't handle graphs with multiple resources of multiple types
  //       .. last one wins.
  //       .. but there shouldn't be any if the incoming data is AS2
  $resources = $incoming->resources();
  foreach($resources as $resource){
    if(in_array("as:Place", $resource->types())){
      $graph = "https://rhiaro.co.uk/places/";
    }
  }

  return $graph;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  // AUTH FIRST
  // Verify token
  $headers = apache_request_headers();
  if(isset($headers['Authorization'])) {
    $token = $headers['Authorization'];

    /* !!!!! */
    if($token == $EP_KEY){
      $response = array("me"=>"https://rhiaro.co.uk/#me", "scope"=>"post", "issued_by"=>"localhost");
    }elseif($token == $GUEST_KEY){
      $response = array("me"=>"http://csarven.ca/#i", "scope"=>"post", "issued_by"=>"https://rhiaro.co.uk/sloph");
    }else{
    /* !!!!! */

      $response = verify_token($token);
    }
    $me = @$response['me'];
    $iss = @$response['issued_by'];
    $client = @$response['client_id'];
    $scope = @$response['scope'];

  }else{
    header("HTTP/1.1 403 Forbidden");
    echo "403: No authorization header set.";
    echo "\n... TODO: dump publicly accessible posts here";
    exit;
  }

  if(empty($response)){
    header("HTTP/1.1 401 Unauthorized");
    echo "401: Access token could not be verified.";
    exit;
  }elseif((stripos($me, "rhiaro.co.uk") === false && !is_guest($me)) || $scope != "post"){
    header("HTTP/1.1 403 Forbidden");
    var_dump($me);
    var_dump(is_guest($me));
    echo "403: Access token was not valid.";
    echo "\n... TODO: dump publicly accessible posts here";
    exit;
  }else{
    // Good to go
    // Do stuff
    $post = file_get_contents('php://input');
    if(empty($post)){
      $post = $_POST;
    }

    if(isset($post) && !empty($post)){

    // Get content-type header
    if($headers["Content-Type"] == "application/x-www-form-urlencoded"){
      // if it's form-encoded convert to json-ld
      // TODO: actual conversion
      if(!isset($post["@context"])){ $post["@context"] = "https://rhiaro.co.uk/vocab"; }
      if(isset($post['access_token'])) unset($post['access_token']);

    }elseif($headers["Content-Type"] == "application/activity+json"){
      // if it's activity+json add default context
      if(!isset($post["@context"])){ $post["@context"] = "https://www.w3.org/ns/activitystreams#"; }

    }elseif($headers["Content-Type"] == "application/ld+json"){
      // pass
    }else{
      header("HTTP/1.1 415 Unsupported Media Type");
      echo "415: Unsupported media type";
      exit;
    }

    // Find slug
    $slug = null;
    if(isset($post['slug'])){
      $slug = urlencode($post['slug']);
      unset($post['slug']);
    }elseif(isset($headers['Slug'])){
      $slug = urlencode($headers['Slug']);
    }

    // parse and validate
    $data = json_encode($post);
    if(!$data){
      header("HTTP/1.1 400 Bad Request");
      echo "400: Bad JSON-LD";
      exit;
    }
    $g = new EasyRdf_Graph();
    $g->parse($post, "jsonld");
    $resources = $g->resources();
    $named = new EasyRdf_Graph();
    foreach($resources as $id => $data){
      if($g->resource($id)->isBNode()){
        $new_uri = make_uri($ep, $g->resource($id));
        $s = new EasyRdf_Resource($new_uri);
        $ps = $data->properties();
        foreach($ps as $p){
          $os = $g->all($id, $p);
          foreach($os as $o){
            $named->add($s, $p, $o);
          }
        }
      }
    }
    // TODO: add default data like author etc
    // insert
    $graph = graph_route($me, $named);
    $ntriples = $named->serialise("ntriples");
    $q = query_insert_n($ntriples, $graph);
    $res = execute_query($ep, $q);
    if(isset($res["t_count"])){
      header("HTTP/1.1 201 Created");
      header("Location: ".$new_uri);
      echo "201 Created: ".$new_uri;
    }

    }else{
      header("HTTP/1.1 400 Bad Request");
      echo "400: Nothing posted";
    }

  }
}elseif($_SERVER['REQUEST_METHOD'] === 'GET'){

  $guest = false;
  if(isset($_GET['guest']) && $_GET['guest'] == '1'){
    $guest = true;
  }

  $result = on_get($ep, $guest);
  $header = $result['header'];
  $content = $result['content'];
  if(gettype($content) == "string"){

    header($header);
    echo $content;

  }else{
    echo "<h1>rhiaro outbox</h1>";
    echo "<p>If you are a regular human, you probably want to look at <a href=\"https://rhiaro.co.uk/\">the homepage</a>..</p>";
    echo "<h2>TODO</h2>";
    echo "<ul>";
    echo "  <li>A human readable feed of all posts instead of this dump.</li>";
    echo "  <li>Paging. Currently this gets everything and just cuts off at 15k triples.</li>";
    echo "  <li>Embed all post data. Currently it fetches type and published, and if applicable target, object and inReplyTo.</li>";
    echo "</ul>";
    echo $content->dump();
  }
}

?>