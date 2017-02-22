<?
require_once('../init.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

if(isset($_GET['start'])){ $start = $_GET['start']; }
else{ 
  $qlatest = query_select_s_desc(1);
  $latest = execute_query($ep, $qlatest);
  $start = $latest['rows'][0]['s'];
}

if(isset($_GET['length'])){ $length = $_GET['length']; }
else { $length = 10; }

if(isset($_GET['type'])){ $types = explode(",", $_GET['type']); }
else { $types = array(null); }

foreach($types as $type){
  $qs[] = query_select_s_prev_of_type_count($start, $length, $type);
}
$results["variables"] = array("s");
$results["rows"] = array();
foreach($qs as $q){
  $res = execute_query($ep, $q);
  $results["rows"] = array_merge($results["rows"], $res["rows"]);
}

$html = "";
$uris = select_to_list($results);
$sorted_all = construct_and_sort($ep, $uris, "as:published");
$sorted_all_uris = array_keys($sorted_all);
$sorted = array_slice($sorted_all, 0, $length);


/* AS2 CollectionPage */
$pageuri = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

/* Prev and Next URIs */
end($sorted);
$next_start = key($sorted);
$first_i = array_search($start, $sorted_all_uris);
if($first_i - $length >= 0){ $back = $first_i-$length; }
else{ $back = 0; }
// $prev_start = array_slice($sorted_all, $back, 1); // TODO

/* Get the URI of the collection (vs the URI of the page) */
/*
    parts of collection url: type
    parts of page url: collection + start, length
*/
$parseduri = parse_url($pageuri);
$collectionuri = $parseduri['scheme']."://".$parseduri['host'].$parseduri['path'];
$params = explode("&", $parseduri['query']);
$collectionparams = array();
foreach($params as $p){
  $kv = explode("=", $p);
  if($kv[0] == "type"){
    $collectionparams[] = $p;
  }
}
if(count($collectionparams > 0)){
  $collectionuri .= "?".implode($collectionparams, "&");
}

$content = new EasyRdf_Graph($collectionuri);
$content->parse($sorted, 'php');

$content->add($pageuri, 'rdf:type', 'as:CollectionPage');
$content->add($pageuri, 'as:summary', "a page of this collection of length ".$length);
$content->addResource($pageuri, 'as:partOf', $collectionuri);
$content->addResource($pageuri, 'as:next', $collectionuri."&length=".$length."&start=".$next_start);
// $content->addResource($pageuri, 'as:prev', $collectionuri."&start=".$prev_start); // TODO
// $content->add($pageuri, 'as:startIndex', $first_i); // TODO
foreach($sorted as $item => $data){
  $content->addResource($pageuri, 'as:items', $item);
}
$content->addResource($collectionuri, 'rdf:type', 'as:Collection');
$content->add($collectionuri, 'as:summary', "A collection containing types ".implode($types, ", "));

/* Conneg */
$result = conneg($acceptheaders, $content);

if(gettype($result['content']) == "string"){
  header($result['header']);
  echo $result['content'];
}else{

  foreach($sorted as $uri => $r){

    $g = new EasyRdf_Graph($uri);
    $g->parse(array($uri=>$r), 'php');
    $resource = $g->resource($uri);
    $resource = set_views($ep, $g->resource());
    $resource = $g->toRdfPhp();

    ob_start();
    include '../../views/'.view_router($resource).'.php';
    $html .= ob_get_clean();
    
    $prevpg = $uri;
  }
  $html .= "<nav id=\"nav\"><p><a href=\"".$next_start."\" id=\"next\" rel=\"next\">earlier</a></p></nav>";

  $return = $html;
  header("Content-Type: text/html");
  echo $return;
  return $return;
}
?>