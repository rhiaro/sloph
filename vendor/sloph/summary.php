<?
function get_posts($ep, $from, $to){
  $q = construct_between($from, $to);
  $res = execute_query($ep, $q);
  if($res){
    return $res;
  }else{
    // var_dump($res);
    // echo "hello";
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

    $cheapest = array("amountEur" => 0);
    $dearest = array("amountEur" => 0);
    $free = 0;

    $out['total'] = count($typed);
    $out['totalusd'] = 0;
    $out['totalgbp'] = 0;
    $out['totaleur'] = 0;
    $out['expensed'] = 0;
    $out['currencies'] = array();

    $out['food'] = array();
    $out['food']['groceriesEur'] = 0;
    $out['food']['foodEur'] = 0;

    foreach($typed as $uri => $post){
        $expensed = get_value(array($uri=>$post), "asext:expensedTo");
        $cost = get_value(array($uri=>$post), "asext:cost");
        $usd = get_value(array($uri=>$post), "asext:amountUsd");
        $eur = get_value(array($uri=>$post), "asext:amountEur");
        $gbp = get_value(array($uri=>$post), "asext:amountGbp");
        if(!is_numeric($usd)){ $usd = 0; }
        if(!is_numeric($eur)){ $eur = 0; }
        if(!is_numeric($gbp)){ $gbp = 0; }
        if(!isset($expensed)){
          $out['totalusd'] += $usd;
          $out['totaleur'] += $eur;
          $out['totalgbp'] += $gbp;
        }else{
          $out['expensed'] += $eur;
        }

        if($eur == 0){
            $free += 1;
        }else{
          if($cheapest['amountEur'] == 0 || $eur < $cheapest['amountEur']){
            $cheapest['amountEur'] = $eur;
            $cheapest['uri'] = $uri;
            $cheapest['content'] = get_value(array($uri=>$post), "as:content");
          }
          if($dearest['amountEur'] == 0 || $eur > $dearest['amountEur']){
            $dearest['amountEur'] = $eur;
            $dearest['uri'] = $uri;
            $dearest['content'] = get_value(array($uri=>$post), "as:content");
          }
        }

        $structured_cost = structure_cost($cost, __DIR__."/currencies.json");
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
        if(in_array("$tagp/food", $tags)){
            $out['food']['foodEur'] += $eur;
        }
        if(in_array("$tagp/groceries", $tags)){
            $out['food']['groceriesEur'] += $eur;
        }
    }

    $out['cheapest'] = $cheapest;
    $out['dearest'] = $dearest;
    $out['free'] = $free;
    if($out['total'] == 0){
      $out['meaneur'] = 0;
    }else{
      $out['meaneur'] = number_format($out['totaleur'] / $out['total'], 2);
    }
    $out['expensed'] = number_format($out['expensed'], 2);

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

        $out['accom'][] = array(
            "uri" => $uri,
            "content" => get_value(array($uri => $post), "as:content"),
            "date" => get_value(array($uri => $post), "as:published"),
            "cost" => get_value(array($uri => $post), "asext:cost")
        );
    }
    if($days > 0){
        $out['accomMean'] = number_format($out['accomEur'] / $days, 2);
    }else{ $out['accomMean'] = "n/a"; }
    if($weeks > 0){
        $out['accomWeeks'] = number_format($out['accomEur'] / $weeks, 2);
    }else{ $out['accomWeeks'] = "n/a"; }
    if($months > 0){
        $out['accomMonth'] = number_format($out['accomEur'] / $months, 2);
    }else{ $out['accomMonth'] = "n/a"; }

    // Tags
    $tags = tally_tags($typed);
    $top = array_slice($tags, 0, 11);
    $others = array_diff_assoc($tags, $top);
    $out['tags'] = count($tags);

    // Get specific stats about food purchases
    if(array_key_exists("$tagp/food", $top)){
        $food = $tags["$tagp/food"];
    }else{ $food = 0; }
    if(array_key_exists("$tagp/groceries", $top)){
        $groceries = $tags["$tagp/groceries"];
    }else{ $groceries = 0; }
    if(isset($tags["$tagp/restaurant"])){
        $rest = $tags["$tagp/restaurant"];
    }else{ $rest = 0; }
    if(isset($tags["$tagp/takeaway"])){
        $take = $tags["$tagp/takeaway"];
    }else{ $take = 0; }
    if($food != 0){
      $restp = $rest / $food * 100;
      $takep = $take / $food * 100;
    }else{
      $restp = 0;
      $takep = 0;
    }

    $out['food']['total'] = $food;
    $out['food']['foodEur'] = number_format($out['food']['foodEur'], 2);
    $out['food']['restaurant'] = number_format($restp, 1);
    $out['food']['takeaway'] = number_format($takep, 1);
    $out['food']['groceries'] = $groceries;
    $out['food']['groceriesEur'] = number_format($out['food']['groceriesEur'], 2);

    // Get random tags
    if(count($others) >= 3){
      $rand = array_rand($others, 3);
      $rand_tags[$rand[0]] = $others[$rand[0]];
      $rand_tags[$rand[1]] = $others[$rand[1]];
      $rand_tags[$rand[2]] = $others[$rand[2]];
      $out['othertags'] = top_tags($rand_tags, 3, $alltags);
      $out['toptags'] = top_tags($top, 6, $alltags);
    }

    // Photos
    if(!empty($photoposts)){
      $randph = array_rand($photoposts);
      $out['photo'] = get_value(array($randph => $photoposts[$randph]), "as:image");
      $out['photodate'] = new DateTime(get_value(array($randph => $photoposts[$randph]), "as:published"));
      $out['photocost'] = get_value(array($randph => $photoposts[$randph]), "asext:cost");
      $out['photocont'] = get_value(array($randph => $photoposts[$randph]), "as:content");
      $perc = count($photoposts) / count($typed) * 100;
      $out['photosp'] = number_format($perc, 1);
    }else{
      $out['photosp'] = 0;
    }

    return $out;

}

function aggregate_consumes($posts, $from, $to, $alltags){
  $typed = get_type($posts, "asext:Consume");
  $diff = $from->diff($to);
  $days = $diff->format("%a");
  $out['total'] = count($typed);
  if($days > 0){
    $out['day'] = $out['total'] / $days;
  }else{
    $out['day'] = 0;
  }

  $tags = tally_tags($typed);
  $top = array_slice($tags, 0, 1);
  $toprest = array_slice($tags, 1, 4);
  $out['top'] = top_tags($top, 1, $alltags);
  $out['toptags'] = top_tags($toprest, 6, $alltags);
  $topar = explode("(", $out['top']);
  $topc = str_replace(")", "", $topar[1]);
  if($days > 0){
    $out['topday'] = $topc / $days;
  }else{
    $out['topday'] = 0;
  }

  $randk = array_rand($typed);
  $out['random'] = "<a href=\"$randk\">".get_value(array($randk => $typed[$randk]), "as:content")."</a>";

  return $out;
}

function aggregate_writing($posts, $from, $to, $alltags){

  $diff = $from->diff($to);
  $days = $diff->format('%a');

  $articles = get_type($posts, "as:Article");
  $notes = get_type($posts, "as:Note");
  $adds = get_type($posts, "as:Add");
  $typed = array_merge($articles, $notes, $adds);

  $reviewposts = array();
  foreach($posts as $uri => $post){
    $tags = get_values(array($uri=>$post), "as:tag");
    if(is_array($tags) && in_array("https://rhiaro.co.uk/tags/week+in+review", $tags)){
      $reviewposts[$uri]['name'] = get_value(array($uri=>$post), "as:name");
    }
  }
  $out['reviewposts'] = $reviewposts;

  global $ep;
  $wrotewords = 0;
  $wroteposts = array();
  $wroteq = query_select_wordcount($from->format(DATE_ATOM), $to->format(DATE_ATOM));
  $wroteres = execute_query($ep, $wroteq);
  foreach($wroteres["rows"] as $res){
    $wrotewords = $wrotewords + $res['wc'];
    $wroteposts[] = $res['p'];
  }

  // Counts
  $out['total'] = count($typed);
  $out['articles'] = count($articles);
  $out['notes'] = count($notes) + count($adds);
  $out['reviews'] = count($reviewposts);
  $out['wrote'] = count($wroteposts);
  $out['wrotetotal'] = $wrotewords;

  $out['words'] = 0;
  foreach($typed as $uri => $post){
    $content = get_value(array($uri=>$post), "as:content");
    $words = explode(" ", $content);
    $out['words'] = count($words) + $out['words'];
  }
  $out['words'] = $out['words'] + $wrotewords;

  if($days > 0){
    $out['dailywords'] = $out['words'] / $days;
    $out['dailynotes'] = $out['total'] / $days;
  }else{
    $out['dailywords'] = 0;
    $out['dailynotes'] = 0;
  }

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
  $topstr = "";
  $limit = count($tags)-1;
  if($max > $limit){ $max = $limit; }
  $top = array_slice($tags, 0, $max, true);
  $last = array_slice($tags, $max, 1, true);
  if(!empty($last)){
    if(count($top) > 1){
      foreach($top as $t => $c){
        $topstr .= "<a href=\"$t\">".$alltags[$t]["name"]."</a> (".$c."), ";
      }
      $topstr .= " and <a href=\"".key($last)."\">".$alltags[key($last)]["name"]."</a> (".$last[key($last)].")";
    }else{
      $topstr = "<a href=\"".key($last)."\">".$alltags[key($last)]["name"]."</a> (".$last[key($last)].")";
    }
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

?>