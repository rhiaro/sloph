<?
require_once('../init.php');

if(isset($_GET['start'])){ $start = $_GET['start']; }
else{ $start = "https://rhiaro.co.uk/2017/01/586de63a68be8"; } // TODO: get latest

if(isset($_GET['length'])){ $length = $_GET['length']; }
else { $length = 10; }

if(isset($_GET['dir'])){ $dir = $_GET['dir']; }
else { $dir = "prev"; }

if($dir == "prev"){
  $q = query_select_s_prev_count($start, $length);
}elseif($dir == "next"){
  $q = query_select_s_next_count($start, $length);
}

$res = execute_query($ep, $q);
$g = new EasyRdf_Graph();
$html = "";
$uris = $res['rows'];

foreach($uris as $r){
  $result = get($ep, $r['s']);
  $content = $result['content'];
  $resource = $content->resource($r['s']);

  $resource = set_views($ep, $content->resource());
  $resource = $content->toRdfPhp();

  ob_start();
  include '../../views/'.view_router($resource).'.php';
  $html .= ob_get_clean();
  
  if(!isset($nextpg)){ $nextpg = $r['s']; }
  $prevpg = $r['s'];
}

$return = json_encode(array("html" => $html, "next" => $nextpg, "prev" => $prevpg));
header("Content-Type: application/json");
echo $return;
return $return;
?>