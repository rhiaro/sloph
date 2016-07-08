<?

function write($data, $date=null, $slug=null){
  $base = $_SERVER["HTTP_HOST"];
  if(!$slug) $slug = uniqid();
  if(!$date) $date = date("ymd-His");
    
  $log = "inbox/".$date."_".$slug.".json";
  $data["@id"] = $base."/".$log;
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

  $h = fopen($log, 'w');
  fwrite($h, $json);
  fclose($h);
  return $log;
}

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

/* Receiving */
$msg = file_get_contents('php://input');
$headers = apache_request_headers();
if(isset($msg) && !empty($msg)){
  if(!supported_content_type($headers["Content-Type"]) || !valid_data($msg)){
    // Not valid JSON so drop it
    header("HTTP/1.1 415 Unsupported Media Type");
  }else{
    $path = write(json_decode($msg, true), date("ymd-His"));
    header("HTTP/1.1 201 Created");
    header("Location: https://rhiaro.co.uk/$path");
  }
}else{

  /* Serving */
  if(supported_content_type($headers["Accept"])){
    $files = scandir("inbox");
    $notifs = array();
    foreach($files as $file){
      if(!is_dir($file) && $file != "." && $file != ".."){
        $notifs[] = array("@id" => "http://rhiaro.co.uk/inbox/".$file);
      }
    }
    $inbox = array("@context" => "http://www.w3.org/ns/ldp#", "@type" => "ldp:Inbox", "ldp:contains" => $notifs);
    $inboxjson = json_encode($inbox, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    header("Content-Type: application/ld+json");
    echo $inboxjson;
  }else{
    header("HTTP/1.1 415 Unsupported Media Type");
  }
}
?>