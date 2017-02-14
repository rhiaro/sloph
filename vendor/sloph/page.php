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

if(isset($_GET['dir'])){ $dir = $_GET['dir']; }
else { $dir = "prev"; }

if(isset($_GET['type'])){ $types = explode(",", $_GET['type']); }
else { $types = array(null); }

foreach($types as $type){
  if($dir == "prev"){
    $qs[] = query_select_s_prev_of_type_count($start, $length, $type);
  }elseif($dir == "next"){
    $qs[] = query_select_s_next_of_type_count($start, $length, $type);
  }
}
$results["variables"] = array("s");
$results["rows"] = array();
foreach($qs as $q){
  $res = execute_query($ep, $q);
  $results["rows"] = array_merge($results["rows"], $res["rows"]);
}

$html = "";
$uris = select_to_list($results);
$sorted = construct_and_sort($ep, $uris, "as:published");
$sorted = array_slice($sorted, 0, $length);

/* AS2 CollectionPage */
$pageuri = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
echo $pageuri;
var_dump($_SERVER['QUERY_STRING']); // HERENOW when use curl it's only keeping the first query param

/* Get the URI of the collection (vs the URI of the page) */
/*
    parts of collection url: type, dir
    parts of page url: start, length
*/
$parseduri = parse_url($pageuri);
var_dump($_GET['type']);
$collectionuri = $parseduri['scheme']."://".$parseduri['host'].$parseduri['path'];
$params = explode("&", $parseduri['query']);
$collectionparams = array();
foreach($params as $p){
  $kv = explode("=", $p);
  if($kv[0] == "type" || $kv[0] == "dir"){
    $collectionparams[] = $p;
  }
}
if(count($collectionparams > 0)){
  $collectionuri .= "?".implode($collectionparams, "&");
}

$content = new EasyRdf_Graph($collectionuri);
$content->parse($sorted, 'php');

// HERENOW
$content->add($pageuri, 'rdf:type', 'as:CollectionPage');
$content->add($pageuri, 'as:summary', "a collection of posts");
$content->addResource($pageuri, 'as:partOf', $collectionuri);

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
    
    if(!isset($nextpg)){ $nextpg = $uri; }
    $prevpg = $uri;
  }
  $html .= "<nav id=\"nav\"><p><a href=\"$nextpg\" id=\"next\" rel=\"next\">later</a></p><p><a href=\"$prevpg\" id=\"prev\" rel=\"prev\">earlier</a></p></nav>";

  // $return = json_encode(array("html" => $html, "next" => $nextpg, "prev" => $prevpg));
  $return = $html;
  header("Content-Type: text/html");
  echo $return;
  return $return;
}
?>