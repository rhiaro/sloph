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
$q2 = query_select_s_between_types($from, $to, array("as:Travel"), "https://blog.rhiaro.co.uk/", False, "as:startTime");
$q3 = query_select_s_between_types($from, $to, array("as:Travel"), "https://blog.rhiaro.co.uk/", False, "as:endTime");

$l1 = select_to_list_sorted(execute_query($ep, $q), 'd');
$l2 = select_to_list_sorted(execute_query($ep, $q2), 'd');
$l3 = select_to_list_sorted(execute_query($ep, $q3), 'd');
$item_uris = array_merge($l1, $l2,$l3);

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

    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $graph = $resource->getGraph();
    $resource = $graph->toRdfPhp();

    $items = get_values($resource, "as:items");
    $timeline = array();
    $now = new DateTime();
    foreach($items as $uri){
      if(has_type(array($uri=>$resource[$uri]), "as:Travel")){
        $sortdate = get_value(array($uri=>$resource[$uri]), "as:startTime");
      }else{
        $sortdate = get_value(array($uri=>$resource[$uri]), "as:published");
      }
      $timeline[$sortdate] = $resource[$uri];
      $timeline[$sortdate]["uri"] = $uri;
    }
    ksort($timeline);
    reset($timeline);

    $prev = array();

    foreach($timeline as $date => $data){
      // Starts with oldest
      $this_date = new DateTime($date);
      if(empty($prev)){
        // It's the first one
        // The height is the diff between this one and the next one;
        //  we'll set that on the next round

        // If it's a checkin get the color
        if(has_type(array($date=>$data), "as:Arrive")){
          $timeline[$date]["color"] = get_checkin_color(array($date=>$data), $locations);
        }else{
          // We dunno what the color is right now.. // TODO
          $timeline[$date]["color"] = "silver";
        }
        $prev[$date] = $data;

      }else{
        // It's not the first one
        // Set the height of the previous one
        $prev_date = array_keys($prev)[0];
        $prev_dt = new DateTime($prev_date);
        $diff = $this_date->getTimestamp() - $prev_dt->getTimestamp();
        $timeline[$prev_date]["diff"] = $diff;

        // If it's a checkin change the color
        if(has_type(array($date=>$data), "as:Arrive")){
          $timeline[$date]["color"] = get_checkin_color(array($date=>$data), $locations);
        }else{
          // Keep the same color as previous
          $timeline[$date]["color"] = $timeline[$prev_date]["color"];
        }
        // Move it on
        unset($prev);
        $prev[$date] = $data;
      }
    }

    // Flip the order so most recent is first.
    krsort($timeline);
    // Set the most recent one which got left out in the loop.
    $latest_date = array_keys($timeline)[0];
    $latest_dt = new DateTime($latest_date);
    $current_location_color = get_checkin_color($last_checkin, $locations);
    $diff_now = $now->getTimestamp() - $latest_dt->getTimestamp();
    $timeline[$latest_date]["color"] = $current_location_color;
    $timeline[$latest_date]["diff"] = $diff_now;

    $markers = array();

    // Set the oldest one
    $start_post = new DateTime(array_keys($timeline)[count($timeline)-1]);
    $start_day = new DateTime($start_post->format("Y-m-d"));
    $start_diff = $start_post->getTimestamp() - $start_day->getTimestamp();
    $markers[$start->format(DATE_ATOM)]["date"] = $start_post;
    $markers[$start->format(DATE_ATOM)]["atom"] = $start_post->format(DATE_ATOM);
    $markers[$start->format(DATE_ATOM)]["diff"] = $start_diff;

    // Set the most recent one
    $now_day = new DateTime($now->format("Y-m-d"));
    $now_day_atom = $now_day->format(DATE_ATOM);
    $now_diff = $now->getTimestamp() - $now_day->getTimestamp();
    $markers[$now_day_atom]["date"] = $now_day;
    $markers[$now_day_atom]["atom"] = $now_day_atom;
    $markers[$now_day_atom]["diff"] = $now_diff;

    // Set the middle
    $day = new DateTime($start_day->format(DATE_ATOM)." + 1 day");
    while($day->getTimestamp() < $now_day->getTimestamp()){
      $day_atom = $day->format(DATE_ATOM);
      $markers[$day_atom]["date"] = $day;
      $markers[$day_atom]["atom"] = $day_atom;
      $markers[$day_atom]["diff"] = 1440*60;
      $day = new DateTime($day->format("Y-m-d")." + 1 day");
    }

    krsort($markers);

    $includes = array('views/timeline.php');
    include 'views/page_template.php';

  }

}catch(Exception $e){
  var_dump($e);
}
?>