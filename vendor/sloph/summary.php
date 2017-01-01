<?
require_once('../init.php');

function aggregate_checkins($ep, $from, $to){

}

function aggregate_acquires($ep, $from, $to){

}

function aggregate_consumes($ep, $from, $to){

}

function aggregate_writing($ep, $from, $to){
  // Tags
  // Specific themes: phd, socialwg, hacking, vegan, travel, star trek
}

function aggregate_socials($ep, $from, $to){
  // Likes
  // Shares
  // Bookmarks
  // Follows?
}

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

$checkins = aggregate_checkins($ep, $from, $to);
$acquires = aggregate_acquires($ep, $from, $to);
$consumes = aggregate_consumes($ep, $from, $to);
$writing = aggregate_writing($ep, $from, $to);
$socials = aggregate_socials($ep, $from, $to);
$total = $checkins['total'] + $acquires['total'] + $consumes['total'] + $writing['total'] + $socials['total'];

include '../../views/summary.php';
?>