<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$archive_uri = "https://rhiaro.co.uk/archive";
$graph = new EasyRdf_Graph($archive_uri);
$resource = $graph->resource($archive_uri);

$types_count = array(
  "as:Article" => array("label" => "articles", "url" => "/articles"), 
  "as:Note" => array("label" => "notes", "url" => "/notes"), 
  "as:Add" => array("label" => "photos and bookmarks", "url" => "/bookmarks"),
  "as:Arrive" => array("label" => "checkins", "url" => "/arrives"),
  "asext:Consume" => array("label" => "food logs", "url" => "/eats"),
  "asext:Acquire" => array("label" => "stuff logs", "url" => "/stuff"),
  "as:Like" => array("label" => "likes", "url" => "/likes"),
  "as:Event" => array("label" => "events", "url" => "/events"),
  "as:Accept" => array("label" => "rsvps", "url" => "/rsvps"),
  "asext:Write" => array("label" => "word count logs", "url" => "/words")
);
foreach($types_count as $type => $data){
  $q = query_count_type($type);
  $res = execute_query($ep, $q);
  $types_count[$type]["count"] = $res["rows"][0]["c"];
}

$dates_count = array();
$now = new DateTime();
$start = new DateTime("2004-01-01");
// $types = array("as:Article", "as:Note", "as:Add");

$types = array("as:Article", "as:Note");
$from = $start->format(DATE_ATOM);
$to = $now->format(DATE_ATOM);
$q = query_select_s_between_types($from, $to, $types);
$res = execute_query($ep, $q);

foreach($res["rows"] as $r){
  $pub = new DateTime($r["d"]);
  $year = $pub->format("Y");
  $month = $pub->format("F");

  if(!isset($dates_count[$year])){
    $dates_count[$year]["total"] = 1;
  }else{
    $dates_count[$year]["total"] += 1;
  }
  if(!isset($dates_count[$year][$month])){
    $dates_count[$year][$month] = 1;
  }else{
    $dates_count[$year][$month] += 1;
  }

}

krsort($dates_count);

require_once('vendor/sloph/header_stats.php');

$g = $resource->getGraph();
$resource = $g->toRdfPhp();

include 'views/top.php';
include 'views/header_stats.php';
include 'views/nav_header.php';
?>

<main class="wrapper w1of1">

  <div id="archive">
    <? include 'views/archive.php'; ?>
  </div>
  <nav><p><a href="#top">top</a></p></nav>
</main>

<?
include 'views/end.php';
?>