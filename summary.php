<?
session_start();
require_once("vendor/init.php");
require_once("vendor/sloph/summary.php");

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$base = "https://rhiaro.co.uk";

$summary_uri = "https://rhiaro.co.uk/summary";
$graph = new EasyRdf_Graph($summary_uri);
$graph->addType($summary_uri, "as:Article");
$graph->add($summary_uri, "as:name", "Summary");
$graph->add($summary_uri, "as:summary", "Aggregation of various logs between two dates.");

$now = new DateTime();
if(isset($_GET['from'])){
  $from = new DateTime($_GET['from']);
}else{
  $from = new DateTime("7 days ago");
}
if(isset($_GET['to'])){
  $to = new DateTime($_GET['to']);
}else{
  $to = $now;
}

$posts = get_posts($ep, $from->format(DATE_ATOM), $to->format(DATE_ATOM));
$tags = get_tags($ep);
$locations = get_locations($ep);
$places = get_places($ep);
$locations = $locations->toRdfPhp();

$checkins = aggregate_checkins($posts, $from, $to, $locations);
$acquires = aggregate_acquires($posts, $from, $to, $tags);
$consumes = aggregate_consumes($posts, $from, $to, $tags);
$writing = aggregate_writing($posts, $from, $to, $tags);
$socials = aggregate_socials($posts, $from, $to);
$travel = aggregate_travel($posts, $from, $to);

$total = $checkins['total'] + $acquires['total'] + $consumes['total'] + $writing['total'] + $socials['total'];

$result = conneg($acceptheaders, $graph);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $resource = $graph->resource($summary_uri);

    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    $includes = array('listing_summary.php');
    include 'views/page_template.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>