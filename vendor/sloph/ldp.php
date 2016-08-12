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

function get($ep, $uri, $content_type="text/html"){
  
  $return = array("header" => null, "content" => null, "errors" => null);
  $acceptheaders = new AcceptHeader($content_type); 
  $graph = get_resource($ep, $uri);

  if(!$graph->isEmpty()){
    foreach($acceptheaders as $accept){
      try{
        $format = EasyRdf_Format::getFormat($accept["raw"]);
        if($format->getSerialiserClass()){
          $out = $graph->serialise($accept["raw"]);
          $return["header"] = "Content-Type: ".$format->getDefaultMimeType();
          $return["content"] = $out;
        }else{
          if($accept["raw"] == "text/html"){
            $return["header"] = "HTTP/1.1 200 OK";
            $return["content"] = $graph;
          }else{
            $return["header"] = "HTTP/1.1 415 Unsupported Media Type";
            $return["content"] = "{$accept["raw"]} is not a supported media type.";
          }
        }
        break;
      }catch(EasyRdf_Exception $e){
        $return["header"] = "HTTP/1.1 415 Unsupported Media Type";
        $return["content"] = "{$accept["raw"]} is not a supported media type.";
      }
    }
  }else{
    $return["header"] = "HTTP/1.1 404 Not Found";
    $return["content"] = "No such resource exists.";
  }
  return $return;
}

function globb($ep, $container, $content_type){

  $return = array("header" => null, "content" => null, "errors" => null);
  $acceptheaders = new AcceptHeader($content_type); 
  $graph = get_resource($ep, $uri);

  $container_types = array("ldp:Container", "as:Collection");

  if(array_intersect($container_types, $graph->types)){
    
  }

  return $return;

}

?>