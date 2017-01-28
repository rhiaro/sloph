<?
require_once('../init.php');

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

foreach($sorted as $uri => $r){
  $content = new EasyRdf_Graph($uri);
  $content->parse(array($uri=>$r), 'php');
  $resource = $content->resource($uri);

  $resource = set_views($ep, $content->resource());
  $resource = $content->toRdfPhp();

  ob_start();
  include '../../views/'.view_router($resource).'.php';
  $html .= ob_get_clean();
  
  if(!isset($nextpg)){ $nextpg = $uri; }
  $prevpg = $uri;
}

$return = json_encode(array("html" => $html, "next" => $nextpg, "prev" => $prevpg));
header("Content-Type: application/json");
echo $return;
return $return;
?>