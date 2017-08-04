<?

/**************************/
/* Getting                */
/*************************/

function get_resource($ep, $uri){

  $graph = new EasyRdf_Graph($uri);

  // Get triples in this ($uri) graph
  $rg = array();
  $qg = query_construct_graph($uri);
  $rg = execute_query($ep, $qg);
  if($rg){
    $graph->parse($rg, 'php', $uri);
    return $graph;
  }

  // If no triples in graph, get triples with $uri as subject
  // Restrict to blog.rhiaro graph.. 
  // TODO: need some way of configuring this and passing multiple
  $r = array();
  $q = query_construct_uri_graph($uri, "https://blog.rhiaro.co.uk/");
  $r = execute_query($ep, $q);
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

  global $_CONTEXT;

  $return = array("header" => null, "content" => null, "errors" => null);

  foreach($acceptheaders as $accept){
    try{
      if($accept["raw"] == "*/*" || !isset($accept["raw"]) || $accept["raw"] == ""){
        $accept["raw"] = "application/ld+json";
        // $accept["raw"] = "text/html";
      }
      $format = EasyRdf_Format::getFormat($accept["raw"]);
      if($format->getSerialiserClass()){

        // For JSON-LD
        $options = array("compact" => true
                        ,"context" => $_CONTEXT

          );

        $out = $graph->serialise($accept["raw"], $options);
        $return["header"] = "Content-Type: ".$format->getDefaultMimeType();
        $return["content"] = $out;
      }else{
        if($accept["raw"] == "text/html"){
          $return["header"] = "HTTP/1.1 200 OK";
          $return["content"] = $graph;
        }else{
          $return["header"] = "HTTP/1.1 406 Not Acceptable";
          $return["content"] = "{$accept["raw"]} is not a supported media type.";
        }
      }
      break;
    }catch(EasyRdf_Exception $e){
      $return["header"] = "HTTP/1.1 406 Not Acceptable";
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

  $current = new EasyRdf_Graph($uri);
  $resource = new EasyRdf_Resource($uri, $current);
  $resource->addLiteral('as:name', "tampering with arrangements");
  $resource->addType('as:Collection');
  $resource->addType('ldp:Container');

  $q = call_user_func_array($query, $params);
  $r = execute_query($ep, $q);

  if($r){
    // $uris = select_to_list($r, array("uri"));

    foreach($r["rows"] as $u){
      // TODO FIXME: this is hardcoded to expect result vars ?s and ?t and now ?l
      $resource->addResource("as:items", $u['s']);
      $resource->addResource("ldp:contains", $u['s']);
      if(isset($u['t'])){
        $current->addResource($u['s'], "rdf:type", $u['t']);
      }
      if(isset($u['l'])){
        $current->addResource($u['s'], "as:location", $u['l']);
      }
    }
  }
  
  return $current;
}

function get_container_dynamic_from_items($ep, $uri, $name="", $items=array()){
  $g = new EasyRdf_Graph($uri);
  $collection[$uri] = array(
      "https://www.w3.org/ns/activitystreams#name" => array(array("value" => $name, "type" => "literal")),
      "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" => array(array("value" => "https://www.w3.org/ns/activitystreams#Collection",
                                                                       "type" => "uri"),
                                                                 array("value" => "http://www.w3.org/ns/ldp#Container",
                                                                       "type" => "uri"),
                                                                ),
    );
  foreach($items as $id => $data){
    $collection[$uri]["https://www.w3.org/ns/activitystreams#items"][] = array("value" => $id, "type" => "uri");
    $g->parse(array($id=>$data), 'php');
  }
  $g->parse($collection, 'php');
  return $g;
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

function get_and_sort($ep, $resources, $by="as:published"){
    $full = array();
    foreach($resources as $resource){
      $r = get($ep, $resource);
      $item = $r['content'];
      if($item) { 
        $item = $item->toRdfPhp(); 
        $full = array_merge($full, $item);
      }
      // else { var_dump($resource); }
    }
    $dates = array();
    foreach($full as $uri => $r){
      $dates[strtotime(get_value(array($uri => $r), $by))] = $uri;
    }
    krsort($dates);
    $order = array_flip($dates);
    $sorted = array_replace($order, $full);
    return $sorted;
  }

/**************************/
/* URIs                   */
/**************************/


function make_uri($ep, $resource, $path=null, $unique=true){

  $base = "https://rhiaro.co.uk/";

  if(!isset($path)){
    $path = path_for_type($resource);
  }

  $done = false;
  $max = 16;
  $fullslug = make_slug($resource);
  while(!$done){
    $slug = decide_when_to_stop($fullslug, $max);
    $uri = $base.$path.$slug;
    
    if(!$unique){ // Sometimes you want the first URI generated from post metadata.
      $done = true;
      return $uri;
    }else{
      // echo "Trying: $uri<br/>";
      if(is_unique($ep, $uri)){
          // echo "Success! Unique.<br/>----<br/>";
          $done = true;
          return $uri;
      }else{
        // echo "Not unique, add another word.<br/>";
        $max = $max + 4;
        // echo "Increase max to $max<br/>";
        if($max >= strlen($fullslug) && strlen($slug) == strlen($fullslug)){
            $done = true;
            // echo "No words left in: $fullslug, add a number.<br/>";
            return increment_slug($ep, $base, $path, $slug, 2);
        }
      }
    }
  }

}

function increment_slug($ep, $base, $path, $slug, $i=2){
  $done = false;
  while(!$done){
    $uri = $base.$path.$slug."-".$i;
    if(is_unique($ep, $uri)){
      $done = true;
      return $uri;
    }else{
      $i = $i+1;
    }
  }
}

function make_slug($resource){
  if($resource->get("as:name") && $resource->get("as:name")->getValue() != ""){
    $string = $resource->get("as:name")->getValue();
  }elseif($resource->get("as:summary") && $resource->get("as:summary")->getValue() != ""){
    $string = strip_tags($resource->get("as:summary")->getValue());
  }elseif($resource->get("as:content") && $resource->get("as:content")->getValue() != ""){
    $string = strip_tags($resource->get("as:content")->getValue());
  }else{
    $string = uniqid();
  }

  $slug = substr(strtolower(str_replace(" ", "-", preg_replace("/[^\w\d \-]/ui", '',strip_tags($string)))), 0);
  return remove_stopwords($slug);
}

function path_for_type($resource){

  if($resource->isA("as:Place")){
    return "location/";
  }elseif($resource->isA("as:Profile") || $resource->isA("as:Person") || $resource->isA("as:Organization")){
    return "contacts/";
  }elseif($resource->get("as:published")){
    $date = new DateTime($resource->get("as:published"));
    return $date->format("Y/m/");
  }elseif($resource->get("as:startTime")){
    $date = new DateTime($resource->get("as:startTime"));
    return $date->format("Y/m/");
  }
  return "";
}

function is_unique($ep, $uri){
  $q = "SELECT ?o WHERE { <$uri> ?p ?o } LIMIT 1";
  $res = $ep->query($q);
  if(empty($res['result']['rows'])){
    return true;
  }else{
    return false;
  }
}

function remove_stopwords($string){
  $stopwords = array("a", "all", "am", "an", "and", "are", "as", "at", "be","but", "by", "etc", "for", "go", "had", "has", "hasnt", "have", "he", "her", "hers", "him", "his", "how", "ie", "if", "in", "into", "is", "it", "its", "me", "my",  "nor", "not", "now", "of", "on", "or", "she", "so", "such", "than", "that", "the", "their", "them", "then", "these", "they", "this", "those", "to", "was", "which", "while", "will", "the", "your", "putting", "you", "might", "i");

  $words = explode("-", $string);
  $filtered = array();
  foreach($words as $word){
    if(!in_array(strtolower($word), $stopwords)){
      array_push($filtered, $word);
    }
  }
  return implode("-", $filtered);
}

function decide_when_to_stop($full_slug, $max=16){
  $words = explode("-", $full_slug);
  $slug = array();
  foreach($words as $word){
      
    if(empty($slug) && strlen($word) >= $max){
      // Add words from the title to the slug
      //echo "Adding $word<br/>";
      array_push($slug, $word);
    }else{
      // Until max is reached
      $current = implode("-", $slug);
      if(strlen($current) + strlen($word) <= $max){
        //echo "Adding $word<br/>";
        array_push($slug, $word);
      }else{
        //echo "No room for $word, break<br/>";
        break;
      }
    }
    //echo "deciding; max: $max, slug:".implode("-",$slug).", sluglen: ".strlen(implode("-",$slug))."<br/>";
  }
  return implode("-", $slug);
}

/**************************/
/* Posting                */
/**************************/

function post($ep, $resource, $target=null, $slug=null){
  if(!isset($target)){
    $target = path_for_type($resource);
  }
  $uri = make_uri($ep, $resource, $target);
  $final = new EasyRdf_Graph($uri);
  $ps = $resource->properties();
  foreach($ps as $p){
    $vs = $resource->all($p);
    foreach($vs as $v){
      $final->add($uri, $p, $v);
    }
  }
  $turtle = $final->serialise('ntriples');
  $q = query_insert($turtle);
  $r = execute_query($ep, $q);
  return $r;
}

?>