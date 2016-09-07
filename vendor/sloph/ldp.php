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

function conneg($acceptheaders, $graph){

  $return = array("header" => null, "content" => null, "errors" => null);

  foreach($acceptheaders as $accept){
    try{
      if($accept["raw"] == "*/*" || !isset($accept["raw"]) || $accept["raw"] == ""){
        $accept["raw"] = "application/ld+json";
      }
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
  return $return;

}

function get($ep, $uri, $content_type="text/html"){

  $acceptheaders = new AcceptHeader($content_type);
  $graph = get_resource($ep, $uri);

  if(!$graph->isEmpty()){
    $return = conneg($acceptheaders, $graph);
  }else{
    $return["header"] = "HTTP/1.1 404 Not Found";
    $return["content"] = false;
  }
  return $return;
}

function get_container_dynamic($ep, $uri, $query, $params, $content_type="text/html"){
  
  $return = array("header" => null, "content" => null, "errors" => null);
  $acceptheaders = new AcceptHeader($content_type);

  $current = new EasyRdf_Graph();
  $resource = new EasyRdf_Resource($uri, $current);
  $resource->addLiteral('as:name', "tampering with arrangements");
  $resource->addType('as:Collection');
  $resource->addType('ldp:Container');

  $q = call_user_func_array($query, $params);
  $r = execute_query($ep, $q);

  if($r){
    $uris = select_to_list($r, array("uri"));

    foreach($uris["rows"] as $u){
      // TODO FIXME: this is hardcoded to expect result vars ?s and ?t
      $resource->addResource("as:items", $u['s']);
      $resource->addResource("ldp:contains", $u['s']);
      if(isset($u['t'])){
        $current->addResource($u['s'], "rdf:type", $u['t']);
      }
    }
  }
  
  $return = conneg($acceptheaders, $current);
  return $return;
}

function get_container($ep, $container, $content_type){

  $return = array("header" => null, "content" => null, "errors" => null);
  $acceptheaders = new AcceptHeader($content_type); 
  $graph = get_resource($ep, $uri);

  $container_types = array("ldp:Container", "as:Collection");

  if(array_intersect($container_types, $graph->types)){
    
  }

  return $return;

}

?>