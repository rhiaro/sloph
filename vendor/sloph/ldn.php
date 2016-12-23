<?
require_once('../init.php');

/*** LDN ***/

function on_get($ep){
  
  $headers = apache_request_headers();
  $ct = $headers["Accept"];
  $acceptheaders = new AcceptHeader($ct);
  $contains = get_container_dynamic($ep, "https://rhiaro.co.uk/incoming/", "query_select_s", array(0, "https://rhiaro.co.uk/incoming/"), $ct);
  $result = conneg($acceptheaders, $contains);
  
  return $result;
}

function on_post($ep, $data){
  var_dump($data);
  return "asdf";
}

/*** Validating input ***/

function supported_content_type($ct){
  $supported = false;
  $sent = explode(",", $ct);
  foreach($sent as $k => $s){
    $sent[$k] = trim($s);
  }
  $cts = array("application/ld+json", "application/activity+json", "text/html");
  $match = array_intersect($sent, $cts);
  if(count($match) > 0){
    $supported = true;
  }
  return $supported;
}

function valid_data($data){
  $valid = false;
  $parsed = json_decode($data, true);
  if($parsed !== null){
    $valid = true;
    // TODO: Valid JSON-LD
  }
  return $valid;
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
  return $json;
}

/*** And... action ***/

header("Accept-Post: application/ld+json");

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  $body = file_get_contents('php://input');
  $headers = apache_request_headers();
  $ct = $headers["Accept"];

  if(isset($_POST) && !empty($_POST)){
  
    $data = this_form($_POST);
    // TODO return back to form
  
  }elseif(isset($body) && !empty($body)){

    $data = $body;

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

  $uri = on_post($ep, $data);
  header("HTTP/1.1 201 Created");
  header("Location: $uri");

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