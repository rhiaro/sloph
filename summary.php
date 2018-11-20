<?
require_once("vendor/init.php");
require_once("vendor/sloph/summary.php");

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
$locations = $locations->toRdfPhp();

$checkins = aggregate_checkins($posts, $from, $to, $locations);
$acquires = aggregate_acquires($posts, $from, $to, $tags);
$consumes = aggregate_consumes($posts, $from, $to, $tags);
$writing = aggregate_writing($posts, $from, $to, $tags);
$socials = aggregate_socials($posts, $from, $to);
$total = $checkins['total'] + $acquires['total'] + $consumes['total'] + $writing['total'] + $socials['total'];

include("views/summary.php");

?>