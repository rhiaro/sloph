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

// Well this is stupid, but having the (broken and useless) CollectionPage in the graph breaks all kinda things.
$page_uri = $g->getUri();
$tmp = $g->toRdfPhp();
unset($tmp[$page_uri]);
$graph = new EasyRdf_Graph($archive_uri);
$graph->parse($tmp);

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

    $graph = $resource->getGraph();
    $resource = $graph->toRdfPhp();

    $items = get_values($resource, "as:items");
    $timeline = array();
    foreach($items as $uri){
      $published = get_value(array($uri=>$resource[$uri]), "as:published");
      $timeline[$published] = $resource[$uri];
      $timeline[$published]["uri"] = $uri;
    }
    krsort($timeline);
    reset($timeline);
    $i = 0;
    while($i < count($timeline)){
      $current = each($timeline);
      $current_date = new DateTime($current["key"]);
      $next = current($timeline);
      $next_date = new DateTime(get_value(array($next["uri"] => $next), "as:published"));
      $diff = $current_date->getTimestamp() - $next_date->getTimestamp();
      $timeline[$current["key"]]["diff"] = $diff;
      $i++;
    }

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