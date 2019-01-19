<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$base = "https://rhiaro.co.uk";

$archive_uri = "https://rhiaro.co.uk/timeline";
$name = "Timeline";
$summary = "Contains banal posts about where I was and what I was eating.";

$now = new DateTime();
$start = new DateTime("-1 month");

$types = array(
  "as:Arrive", 
  "asext:Consume", 
  "asext:Acquire",
  "as:Note",
  "as:Article",
  "as:Add"
);
$from = $start->format(DATE_ATOM);
$to = $now->format(DATE_ATOM);
$q = query_select_s_between_types($from, $to, $types);
$item_uris = select_to_list_sorted(execute_query($ep, $q), 'd');

$next_uri = $prev_uri = "#";
$nav = array("next" => $next_uri, "prev" => $prev_uri);

$g = get_container_dynamic_from_items($ep, $archive_uri, 'as:published', $name, $item_uris, count($item_uris), $nav, true);
$g->add($archive_uri, "view:stylesheet", "views/timeline.css");
$g->addLiteral($archive_uri, "as:summary", $summary);
// TODO: delete the CollectionPage? URI is bad anyway.
// echo $g->dump();

$result = conneg($acceptheaders, $graph);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $resource = $graph->resource($archive_uri);

    require_once('vendor/sloph/header_stats.php');

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';
    include 'views/header_stats.php';
    include 'views/nav_header.php';
?>

    <main class="wrapper w1of1">

      <div id="archive">
        <? include 'views/timeline.php'; ?>
      </div>
      <nav><p><a href="#top">top</a></p></nav>
    </main>

<?
    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>