<?

function write($data, $date=null, $slug=null){
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  
  if(!$slug) $slug = uniqid();
  if(!$date) $date = date("ymd-His");
    
  $log = "inbox/".$date."_".$slug.".json";
  $h = fopen($log, 'w');
  fwrite($h, $data);
  fclose($h);
  return $log;
}

/* Receiving */
$msg = file_get_contents('php://input');
if(isset($msg) && !empty($msg)){
  $post = json_decode($msg, true);
  if($post === null || !isset($post["@context"])){
    // Not valid JSON or doesn't have a context so drop it
    // TODO: check content-type header
    header("HTTP/1.1 415 Unsupported Media Type");
  }else{
    $path = write($msg, date("ymd-His"));
    header("HTTP/1.1 201 Created");
    header("Location: $path");
  }
}else{

  /* Serving */
  // TODO: Check accept header
  // If none or json
  //   serve list as json
  $files = scandir("inbox");
  $notifs = array();
  foreach($files as $file){
    if(!is_dir($file) && $file != "." && $file != ".."){
      $notifs[] = array("@id" => "http://rhiaro.co.uk/inbox/".$file);
    }
  }
  $inbox = array("@context" => "http://www.w3.org/ns/ldp", "@type" => "ldp:Inbox", "ldp:contains" => $notifs);
  $inboxjson = json_encode($inbox, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  header("Content-Type: application/ld+json");
  echo $inboxjson;
  // else
  //   return 415
}
?>