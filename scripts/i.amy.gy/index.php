<?
require_once("AcceptHeader.php");

function make_collection(){

  $dirs = glob("*", GLOB_ONLYDIR);
  $cur = "https://i.amy.gy/";
  $coll = array("@context" => "https://www.w3.org/ns/activitystreams#"
              , "type" => "Collection"
              , "id" => $cur
              , "attributedTo" => "https://rhiaro.co.uk/#me"
              , "totalItems" => count($dirs)
              , "items" => array()
              );
  foreach($dirs as $dir){
    $coll["items"][] = $cur.$dir;
  }
  return $coll;
}

function make_json($collection){
  $json = stripslashes(json_encode($collection, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES));
  return $json;
}

$headers = apache_request_headers();
$content_type = $headers["Accept"];
$acceptheaders = new AcceptHeader($content_type);
$jsonheaders = array("application/ld+json", "application/json", "application/activity+json");
$coll = make_collection();

foreach($acceptheaders as $accept){
  if($accept["raw"] == "*/*" || !isset($accept["raw"]) || $accept["raw"] == ""){
    $accept["raw"] = "application/ld+json";
  }
  if($accept["raw"] == "text/html"){
    break;
  }elseif(in_array($accept["raw"], $jsonheaders)){
    header("Content-Type: application/ld+json");
    echo make_json($coll);
    exit();
  }else{
    header("HTTP/1.1 415 Unsupported Media Type");
  }
}
?>
<!doctype html>
<html>
  <head>
    <title>Photo albums</title>
  </head>
  <body>
    <h1>Photo albums</h1>
    <ul>
    <?foreach($coll["items"] as $i):?>
      <li><a href="https://rhiaro.co.uk/photos?album=<?=$i?>"><?=$i?></a></li>
    <?endforeach?>
    </ul>
  </body>
</html>