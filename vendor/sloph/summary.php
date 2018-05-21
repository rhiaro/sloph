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

function aggregate_acquires($posts, $from, $to, $alltags){
    $typed = get_type($posts, "asext:Acquire");
    $diff = $from->diff($to);
    $days = $diff->format("%a");
    $weeks = floor($days / 7);
    $months = floor($days / 30);

    $tagp = "https://rhiaro.co.uk/tags";
    $accom_posts = array();
    $transit_posts = array();

    $out['total'] = count($typed);
    $out['totalusd'] = 0;
    $out['totalgbp'] = 0;
    $out['totaleur'] = 0;
    $out['currencies'] = array();

    foreach($typed as $uri => $post){
        $cost = get_value(array($uri=>$post), "asext:cost");
        $out['totalusd'] += get_value(array($uri=>$post), "asext:amountUsd");
        $out['totaleur'] += get_value(array($uri=>$post), "asext:amountEur");
        $out['totalgbp'] += get_value(array($uri=>$post), "asext:amountGbp");

        $structured_cost = structure_cost($cost);
        $out['currencies'][] = $structured_cost['currency'];

        $photo = get_value(array($uri=>$post), "as:image");
        if($photo){
          $photoposts[$uri] = $post;
        }

        $tags = get_values(array($uri=>$post), "as:tag");
        if(in_array("$tagp/accommodation", $tags) || in_array("$tagp/shelter", $tags)){
            $accom_posts[$uri] = $post;
        }
        if(in_array("$tagp/transit", $tags) || in_array("$tagp/transport", $tags)){
            $transit_posts[$uri] = $post;
        }
    }

    $out['currencies'] = array_unique($out['currencies']);

    if($weeks > 0) { 
        $out['week'] = number_format($out['totaleur'] / $weeks, 2); 
    }else{ 
        $out['week'] = "n/a"; 
    }
    if($months > 0) { 
        $out['month'] = number_format($out['totaleur'] / $months, 2); 
    }else{ 
        $out['month'] = "n/a"; 
    }
    if($days > 0) { 
        $out['day'] = number_format($out['totaleur'] / $days, 2); 
    }else{ 
        $out['day'] = "n/a"; 
    }

    // Get specific stats for transit
    $out['transitNum'] = count($transit_posts);
    $out['transitEur'] = 0;
    foreach($transit_posts as $uri => $post){
        $eur = get_value(array($uri=>$post), "asext:amountEur");
        $out['transitEur'] += $eur;
    }
    $transit_tags = tally_tags($transit_posts, true);
    if(array_key_exists("$tagp/transit", $transit_tags)){
        unset($transit_tags["$tagp/transit"]);
    }
    if(array_key_exists("$tagp/travel", $transit_tags)){
        unset($transit_tags["$tagp/travel"]);
    }
    if(array_key_exists("$tagp/transport", $transit_tags)){
        unset($transit_tags["$tagp/transport"]);
    }
    if(count($transit_tags) > 0){
        foreach($transit_tags as $tag => $num){
          $means = str_replace($tagp."/", "", $tag);
          $meansAr[] = "$num by $means";
        }
        $out['transitMeans'] = " (".implode(", ", $meansAr).")";
    }else{
        $out['transitMeans'] = "";
    }

    // Get specific stats for accommodation
    $out['accomNum'] = count($accom_posts);
    $out['accomEur'] = 0;
    foreach($accom_posts as $uri => $post){
        $eur = get_value(array($uri=>$post), "asext:amountEur");
        $out['accomEur'] += $eur;
    }

    // Tags
    $tags = tally_tags($typed);
    $top = array_slice($tags, 0, 11);
    $others = array_diff_assoc($tags, $top);
    $out['tags'] = count($tags);

    // Get specific stats about food purchases
    if(array_key_exists("$tagp/food", $top)){
        unset($top["$tagp/food"]);
        $food = $tags["$tagp/food"];
        if(isset($tags["$tagp/restaurant"])){
            $rest = $tags["$tagp/restaurant"];
            unset($top["$tagp/restaurant"]);
        }
        if(isset($tags["$tagp/takeaway"])){
            $take = $tags["$tagp/takeaway"];
            unset($top["$tagp/takeaway"]);
        }
        $restp = $rest / $food * 100;
        $takep = $take / $food * 100;
        $foodstr = ". I bought <a href=\"$tagp/food\">food</a> on ".$food." occasions, ".number_format($restp, 1)."% of the time in <a href=\"$tagp/restaurant\">restaurants</a> and ".number_format($takep, 1)."% of the time for <a href=\"$tagp/takeaway\">takeaway</a>";
    }else{
        $foodstr = "";
    }

    // Get random tags
    $rand = array_rand($others, 3);
    $rand_tags[$rand[0]] = $others[$rand[0]];
    $rand_tags[$rand[1]] = $others[$rand[1]];
    $rand_tags[$rand[2]] = $others[$rand[2]];
    $out['othertags'] = top_tags($rand_tags, 3, $alltags);
    $out['toptags'] = top_tags($top, 6, $alltags).$foodstr;

    // Photos
    $randph = array_rand($photoposts);
    $out['photo'] = get_value(array($randph => $photoposts[$randph]), "as:image");
    $out['photodate'] = new DateTime(get_value(array($randph => $photoposts[$randph]), "as:published"));
    $out['photocost'] = get_value(array($randph => $photoposts[$randph]), "asext:cost");
    $out['photocont'] = get_value(array($randph => $photoposts[$randph]), "as:content");
    $perc = count($photoposts) / count($typed) * 100;
    $out['photosp'] = number_format($perc, 1);

    return $out;

}

function aggregate_consumes($posts, $from, $to, $alltags){
  $typed = get_type($posts, "asext:Consume");
  $diff = $from->diff($to);
  $days = $diff->format("%a");
  $out['total'] = count($typed);
  $out['day'] = $out['total'] / $days;

  $tags = tally_tags($typed);
  $top = array_slice($tags, 0, 1);
  $toprest = array_slice($tags, 1, 4);
  $out['top'] = top_tags($top, 1, $alltags);
  $out['toptags'] = top_tags($toprest, 6, $alltags);
  $topar = explode("(", $out['top']);
  $topc = str_replace(")", "", $topar[1]);
  $out['topday'] = $topc / $days;

  $randk = array_rand($typed);
  $out['random'] = "<a href=\"$randk\">".get_value(array($randk => $typed[$randk]), "as:content")."</a>";

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
  $tags = tally_tags($typed);
  $out['toptags'] = top_tags($tags, 11, $alltags);
  $out['tags'] = count($tags);

  return $out;
}

function tally_tags($posts){
  $tags = array();
  foreach($posts as $uri=>$post){
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
  return $tags;
}

function top_tags($tags, $max, $alltags){
  $limit = count($tags)-1;
  if($max > $limit){ $max = $limit; }
  $top = array_slice($tags, 0, $max, true);
  $last = array_slice($tags, $max, 1, true);
  $topstr = "";
  if(count($top) > 1){
    foreach($top as $t => $c){
      $topstr .= "<a href=\"$t\">".$alltags[$t]["name"]."</a> (".$c."), ";
    }
    $topstr .= " and <a href=\"".key($last)."\">".$alltags[key($last)]["name"]."</a> (".$last[key($last)].")";
  }else{
    $topstr = "<a href=\"".key($last)."\">".$alltags[key($last)]["name"]."</a> (".$last[key($last)].")";
  }
  return $topstr;
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
$acquires = aggregate_acquires($posts, $from, $to, $tags);
$consumes = aggregate_consumes($posts, $from, $to, $tags);
$writing = aggregate_writing($posts, $from, $to, $tags);
$socials = aggregate_socials($posts, $from, $to);
$total = $checkins['total'] + $acquires['total'] + $consumes['total'] + $writing['total'] + $socials['total'];

include '../../views/summary.php';
?>