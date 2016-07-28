<?

function get_resource($ep, $uri){
  
  $r = array();
  $q = query_construct($uri);
  $r = execute_query($ep, $q);

  $graph = new EasyRdf_Graph($uri);
  $graph->parse($r, 'php', $uri);
  // Get primaryTopic of this URI as well.
  $pt = $graph->primaryTopic($uri);
  if($pt){
    $ptUri = $pt->getUri();
    $qpt = query_construct($ptUri);
    $rpt = execute_query($ep, $qpt);
    $graph->parse($rpt, 'php', $ptUri);
  }

  return $graph;
}

function get($ep, $uri, $ct="text/html"){
  
  $return = array("header" => null, "content" => null, "errors" => null);
  
  $graph = get_resource($ep, $uri);
  if(!$graph->isEmpty()){
    $format = EasyRdf_Format::getFormat($ct);
    if($format->getSerialiserClass()){
      $out = $graph->serialise($ct);
      $return["header"] = "Content-Type: ".$format->getDefaultMimeType();
      $return["content"] = $out;
    }else{
      if($ct == "text/html"){
        $return["header"] = "HTTP/1.1 200 OK";
        $return["content"] = $graph->dump();
      }else{
        $return["header"] = "HTTP/1.1 415 Unsupported Media Type";
        $return["content"] = "$ct is not a supported media type.";
      }
    }
  }else{
    $return["header"] = "HTTP/1.1 404 Not Found";
    $return["content"] = "No such resource exists.";
  }
  return $return;
}

?>