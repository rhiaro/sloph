<?
require_once('../init.php');

/*** LDN ***/

function on_get($ep){
  
  $headers = apache_request_headers();
  $ct = $headers["Accept"];
  $acceptheaders = new AcceptHeader($ct);
  $contains = get_container_dynamic($ep, "https://rhiaro.co.uk/incoming/", "query_select_s", array(0, "https://rhiaro.co.uk/incoming/"), $ct);
  $result = conneg($acceptheaders, $contains);
  $header = $result['header'];
  $content = $result['content'];

  return $content;
}

function on_post(){

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
  $json = array("@context" => "https://www.w3.org/ns/activitystreams", "generator" => "https://rhiaro.co.uk/sloph", "content" => "");
  $json['content'] = $post['content'];
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);
  return $json;
}

function webmentionio($json){
  // TODO
  // Use webmention.io webhook to LDN these up
  return $json;
}

if(isset($_POST) && !empty($_POST)){
  var_dump(this_form($_POST));
}else{
  $content = on_get($ep);
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $contains = $content->toRdfPhp();
    include '../../views/incoming.php';
  }
}

?>