<?
require_once('../init.php');

function get_posts($ep, $from, $to){
  $q = construct_between($from, $to);
  $res = execute_query($ep, $q);
  if($res){
    return $res;
  }else{
    var_dump($res);
    return array();
  }
}

function get_type($posts, $type){
  $out = array();
  foreach($posts as $uri => $post){
    if(has_type(array($uri=>$post), $type)){
      $out[$uri] = $post;
    }
  }
  return $out;
}

function aggregate_checkins($posts, $from, $to, $locations){
  $typed = get_type($posts, "as:Arrive");
  $typed = array_reverse($typed);
  $num = array();
  foreach($typed as $uri => $post){
    $location = get_value(array($uri => $post), "as:location");
    if(array_key_exists($location, $locations)){
      // Only count vague checkins
      $post['uri'] = $uri;
      $num[] = $post;
    }
  }
  
  // Total durations
  $tally = array();
  foreach($num as $i => $post){
    $location = get_value(array($post['uri'] => $post), "as:location");
    $date = new DateTime(get_value(array($post['uri']=>$post), "as:published"));
    if(isset($num[$i+1])){
      $next = $num[$i+1];
      $nextdate = new DateTime(get_value(array($next['uri']=>$next), "as:published"));
    }else{
      $nextdate = $to;
    }
    $seconds = $nextdate->getTimestamp() - $date->getTimestamp();
    if(isset($tally[$location])){
      $tally[$location] += $seconds;
    }else{
      $tally[$location] = $seconds;
    }

  }
  $out['top'] = array();
  arsort($tally);
  foreach($tally as $location => $seconds){
    $d1 = new DateTime("@$seconds");
    $d2 = new DateTime("@0");
    $duration = time_diff_to_human($d1, $d2);
    $out['top'][] = array("location" => $location, "duration" => $duration, "label" => str_replace("her", "my", str_replace("is ", "", get_value(array($location=>$locations[$location]), "blog:presentLabel"))));
  }

  $out['total'] = count($typed);
  return $out;
}

function aggregate_acquires($posts, $from, $to){
  $typed = get_type($posts, "asext:Acquire");
  $out['total'] = count($typed);
  return $out;
}

function currency_to_gbp($date, $currency, $amount){
  $d = $date->format("Y-m-d");
  $ep = "https://api.fixer.io/".$d."?base=GBP";
  $rates = file_get_contents($ep);
  $rates = json_decode($rates, true);
  if(isset($rates["rates"][strtoupper($currency)])){
    $rate = $rates["rates"][strtoupper($currency)];
    return $amount / $rate;
  }
  return false;
}

function aggregate_consumes($posts, $from, $to){
  $typed = get_type($posts, "asext:Consume");
  $out['total'] = count($typed);
  return $out;
}

function aggregate_writing($posts, $from, $to, $alltags){

  $diff = $from->diff($to);
  $days = $diff->format('%a');

  $articles = get_type($posts, "as:Article");
  $notes = get_type($posts, "as:Note");
  $typed = array_merge($articles, $notes);

  // Counts
  $out['total'] = count($typed);
  $out['articles'] = count($articles);
  $out['notes'] = count($notes);
  
  $out['words'] = 0;
  foreach($typed as $uri => $post){
    $content = get_value(array($uri=>$post), "as:content");
    $words = explode(" ", $content);
    $out['words'] = count($words) + $out['words'];
  }

  $out['dailywords'] = $out['words'] / $days;
  $out['dailynotes'] = $out['total'] / $days;

  // Tags
  $tags = array();
  foreach($typed as $uri=>$post){
    $ts = get_values(array($uri=>$post), "as:tag");
    if(is_array($ts)){
      foreach($ts as $t){
        if(substr($t, 0, 4) == "http"){
          if(isset($tags[$t])){
            $tags[$t]++;
          }else{
            $tags[$t] = 1;
          }
        }
      }
    }
  }
  arsort($tags);
  $max = count($tags)-1;
  if($max > 11){ $max = 11; }
  $top = array_slice($tags, 0, $max, true);
  $last = array_slice($tags, $max, 1, true);
  $out['toptags'] = "";
  foreach($top as $t => $c){
    $out['toptags'] .= "<a href=\"$t\">".$alltags[$t]["name"]."</a> (".$c."), ";
  }
  $out['toptags'] .= " and <a href=\"".key($last)."\">".$alltags[key($last)]["name"]."</a> (".$last[key($last)].")";
  $out['tags'] = count($tags);

  return $out;
}

function aggregate_socials($posts, $from, $to){
  // Likes
  // Shares
  // Bookmarks
  // Follows?
  $typed = array_merge(get_type($posts, "as:Like"), get_type($posts, "as:Announce"), get_type($posts, "as:Add"), get_type($posts, "as:Follow"));
  $out['total'] = count($typed);
  return $out;
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

$posts = get_posts($ep, $from->format(DATE_ATOM), $to->format(DATE_ATOM));
$tags = get_tags($ep);
$locations = get_locations($ep);
$locations = $locations->toRdfPhp();

$checkins = aggregate_checkins($posts, $from, $to, $locations);
$acquires = aggregate_acquires($posts, $from, $to);
$consumes = aggregate_consumes($posts, $from, $to);
$writing = aggregate_writing($posts, $from, $to, $tags);
$socials = aggregate_socials($posts, $from, $to);
$total = $checkins['total'] + $acquires['total'] + $consumes['total'] + $writing['total'] + $socials['total'];

include '../../views/summary.php';
?>