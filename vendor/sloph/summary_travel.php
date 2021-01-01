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

function get_places($ep){
    $q = query_construct_graph("https://rhiaro.co.uk/places/");
    $res = execute_query($ep, $q);
    if($res){
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
  // final/current place
  $final = get_value(array($filtered[0]["uri"]=>$filtered[0]), "as:target");
  $final_date = get_value(array($filtered[0]["uri"]=>$filtered[0]), "as:endTime");
  $now = new DateTime();
  $by_place[$final]["visits"][] = array(
                                    "startDate" => $final_date,
                                    "endDate" => $now->format(DATE_ATOM),
                                    "uris" => [$filtered[0]["uri"]]
                                    );

  foreach($filtered as $i => $post){

    $origin = get_value(array($post["uri"]=>$post), "as:origin");
    $departed = get_value(array($post["uri"]=>$post), "as:startTime");
    if(isset($filtered[$i+1])){
        $prev = $filtered[$i+1];
        $target = get_value(array($prev["uri"]=>$prev), "as:target");
        $arrived = get_value(array($prev["uri"]=>$prev), "as:endTime");
        $prev_uri = $prev["uri"];
    }else{
        $target = $origin;
        $arrived = "1990-08-10T05:00:00+01:00";
        $prev_uri = "";
    }

    if($origin == $target){
      $by_place[$origin]["visits"][] = array(
                                            "startDate" => $arrived,
                                            "endDate" => $departed,
                                            "uris" => [$post["uri"], $prev_uri]
                                            );
    }

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

function generate_map_data($visits, $places){
    $out = array();
    foreach($visits as $place => $data){
        if(isset($places[$place])){
            $name = get_value(array($place=>$places[$place]), "as:name");
            $lat = get_value(array($place=>$places[$place]), "as:latitude");
            $lng = get_value(array($place=>$places[$place]), "as:longitude");
            $coords = array((float)$lat, (float)$lng);

            $visits[$place]["name"] = $name;
            $visits[$place]["coordinates"] = $coords;

            $out[] = $visits[$place];
        }
    }

    return json_encode($out, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

?>