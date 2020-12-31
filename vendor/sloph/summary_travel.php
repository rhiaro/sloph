<?
/***********************
Data processing related to travel/journeys
***********************/

function get_travels($ep){
  $q = query_construct_type("as:Travel", "as:startTime");
  $res = execute_query($ep, $q);
  if($res){
    // krsort($res);
    return $res;
  }else{
    return array();
  }
}

function time_in_places($posts){
  $filtered = array();
  foreach($posts as $uri => $post){
    $origin = get_value(array($uri=>$post), "as:origin");
    $target = get_value(array($uri=>$post), "as:target");
    if($origin && $target){
        $post["uri"] = $uri;
        $filtered[] = $post;
    }
  }

  $by_place = array();
  foreach($filtered as $i => $post){
    $prev = $filtered[$i+1];
    $origin = get_value(array($post["uri"]=>$post), "as:origin");
    $departed = get_value(array($post["uri"]=>$post), "as:startTime");
    $target = get_value(array($prev["uri"]=>$prev), "as:target");
    $arrived = get_value(array($prev["uri"]=>$prev), "as:endTime");

    if($origin == $target){
      $by_place[$origin]["visits"][] = array(
                                            "startDate" => $arrived,
                                            "endDate" => $departed,
                                            "uris" => [$post["uri"], $prev["uri"]]
                                            );
    }else{

    }
    // echo "<p>";
    // echo "From ".$origin." to ".get_value(array($post["uri"]=>$post), "as:target");
    // echo "</p>";


  }

  return $by_place;
}

function index_by_origin($posts){
  $by_origin = array();
  foreach($posts as $uri => $post){
    $origin = get_value(array($uri=>$post), "as:origin");
    $by_origin[$origin][$uri] = $post;
  }
  return $by_origin;
}

?>