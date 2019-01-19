<?

function view_router($resource){
    global $ep;
    if(has_type($resource, "as:Add") || has_type($resource, "as:Like") || has_type($resource, "as:Announce") || has_type($resource, "as:Follow")){
      if(has_type($resource, "as:Add") && in_array("asext:Album", get_types($ep, get_value($resource, "as:target")))){
        return 'objects';
      }
      return 'link';
    }elseif(has_type($resource, "as:Arrive")){
      return 'checkin';
    }elseif(has_type($resource, "as:Travel") && get_value($resource, "as:origin") && get_value($resource, "as:target")){
      return 'travel';
    }elseif(has_type($resource, "asext:Consume") || has_type($resource, "asext:Acquire")){
      return 'stuff';
    }elseif(has_type($resource, "as:Invite") || has_type($resource, "as:Accept") || has_type($resource, "as:Event")){
      return 'event';
    }elseif(has_type($resource, "as:Collection") || has_type($resource, "as:CollectionPage")){
      return 'collection';
    }elseif(has_type($resource, "asext:Write")){
      return 'words';
    }elseif(has_type($resource, "as:Place")){
      return 'place';
    }else{
      if(count($resource) > 1){
        return 'index';
      }
      return 'article';
    }
}

function collection_sort_predicate($collection){

  $mapping = array(
    "https://rhiaro.co.uk/location" => "as:name",
    "https://rhiaro.co.uk/locations" => "as:name"
  );
  if(array_key_exists($collection, $mapping)){
    return $mapping[$collection];
  }

  return "as:published";
}

function collection_items_graph($collection){
  $mapping = array(
    "https://rhiaro.co.uk/location" => "https://rhiaro.co.uk/locations/",
    "https://rhiaro.co.uk/locations" => "https://rhiaro.co.uk/locations/"
  );
  if(array_key_exists($collection, $mapping)){
    return $mapping[$collection];
  }

  return "https://blog.rhiaro.co.uk/";
}

/**********************/
/* Visual things      */
/**********************/

function get_main_theme($ep){
  $q = query_for_theme();
  $r = execute_query($ep, $q);
  if($r["rows"]){
    return $r["rows"][0];
  }else{
    return array("color"=>"silver","image"=>"");
  }
}

function get_icon($resource){
  $types = get_values($resource, "rdf:type");
  foreach($types as $type){
    $ns = new EasyRdf_Namespace();
    $type = $ns->shorten($type);
    if(has_type($resource, "as:Add") && has_type($resource, "as:target") == "https://rhiaro.co.uk/bookmarks/"){
      $t = "&#128278;";
    }else{
      $t = get_icon_from_type($type);
    }
    if($t) { return $t; }
  }
}

function get_icon_from_type($type, $skip=array()){
  $icons = array(
       "asext:Consume" => "&#127860;"
      ,"asext:Acquire" => "&#128176;"
      ,"asext:Sleep" => "&#128164;"
      ,"as:Article" => "&#128196;"
      ,"as:Note" => "&#128493;"
      ,"as:Like" => "&#10030;"
      ,"as:Add" => "&#43;"
      ,"as:Announce" => "&#128257;"
      ,"as:Arrive" => "&#11165;"
      ,"as:Follow" => "&#128483;"
      ,"as:Event" => "&#128467;"
      ,"as:Accept" => "&#128467;"
      ,"as:Invite" => "&#128467;"
      ,"as:Travel" => "&#10239;"
      ,"as:Object" => "&#133;"
    );
  if(!is_array($type)){
    $type = array($type);
  }
  foreach($type as $t){
    if(isset($icons[$t]) && !in_array($t, $skip)){
      return $icons[$t];
    }else{
      return false;
    }
  }
}

function get_project_icons($ep){
  $icons = array(
    array(
      array("name" => "Open Data Services (2018-now)", "uri" => "https://opendataservices.coop",
        "icon" => "views/icon_ods.png", "color" => ""),
      array("name" => "Digital Bazaar (2018-now)", "uri" => "https://digitalbazaar.com",
        "icon" => "views/icon_db.png", "colour" => ""),
      array("name" => "W3C Technical Architecture Group (Jan 2021-now)", "uri" => "https://www.w3.org/2001/tag/",
        "icon" => "views/icon_tag.png", "color" => ""),
      array("name" => "NaNoWriMo", "uri" => "https://nanowrimo.org/participants/rhiaro",
        "icon" => "views/icon_nanowrimo.png", "color" => "white"),
    ),
    array(
      array("name" => "Social Web Protocols (editor)", "uri" => "https://w3.org/TR/social-web-protocols/",
        "icon" => "views/icon_swp.png", "color" => "white"),
      array("name" => "ActivityPub (coauthor)", "uri" => "https://w3.org/TR/activitypub/",
        "icon" => "views/icon_ap.png", "color" => "white"),
      array("name" => "Linked Data Notifications (coeditor)", "uri" => "https://w3.org/TR/ldn/",
        "icon" => "views/icon_ldn.png", "color" => "white"),
      array("name" => "LinkedResearch (bystander)", "uri" => "https://linkedresearch.org/",
        "icon" => "views/icon_lr.png", "color" => "white"),
      array("name" => "dokieli (occasional contributor)", "uri" => "https://dokie.li/",
        "icon" => "views/icon_do.png", "color" => "white"),
      array("name" => "Indieweb (bystander)", "uri" => "https://indieweb.org/User:Rhiaro.co.uk",
        "icon" => "views/icon_iwc.png", "color" => "white"),
    ),
    array(
      array("name" => "OCCRP (2017-2018)", "uri" => "https://occrp.org",
        "icon" => "views/icon_occrp.png", "color" => ""),
      array("name" => "W3C (2016-2018)", "uri" => "https://w3.org",
        "icon" => "views/icon_w3c.png", "color" => "white"),
      array("name" => "MIT CSAIL (2015-2016)", "uri" => "http://dig.csail.mit.edu/",
        "icon" => "views/icon_mit.png", "color" => "white"),
      array("name" => "The Solid Project (2015-2016)", "uri" => "https://solid.mit.edu",
        "icon" => "views/icon_solid.png", "color" => ""),
      array("name" => "University of Edinburgh (2011-2017)", "uri" => "https://www.ed.ac.uk/informatics",
        "icon" => "views/icon_edi.png", "color" => "white"),
      array("name" => "SOCIAM (2014-2017)", "uri" => "https://sociam.org/",
        "icon" => "views/icon_sociam.png", "color" => "white"),
      array("name" => "Prewired (cofounder, 2012-2016)", "uri" => "https://prewired.org",
        "icon" => "views/icon_prewired.png", "color" => "white"),
      array("name" => "SocieTea (el PresidenTea, 2012-2014)", "uri" => "https://edinburghsocietea.co.uk",
        "icon" => "views/icon_societea.png", "color" => "white"),
      array("name" => "BBC (2014)", "uri" => "http://www.bbc.co.uk/blogs/internet/tags/linked-data",
        "icon" => "views/icon_bbc.png", "color" => "white"),
      array("name" => "University of Lincoln (2008-2011)", "uri" => "https://www.lincoln.ac.uk/home/socs/",
        "icon" => "views/icon_lincoln.png", "color" => "white"),
    ),
  );
  // TODO: get projects from store
  return $icons;
}

function get_travel_icon($tag){
  $icons = array(
     "https://rhiaro.co.uk/tags/bus" => "&#128652;"
    ,"https://rhiaro.co.uk/tags/car" => "&#128664;"
    ,"https://rhiaro.co.uk/tags/plane" => "&#9992;"
    ,"https://rhiaro.co.uk/tags/boat" => "&#128741;"
    ,"https://rhiaro.co.uk/tags/walk" => "&#128694;"
    ,"https://rhiaro.co.uk/tags/train" => "&#128645;"
  );
  if(isset($icons[$tag])){
    return $icons[$tag];
  }else{
    return false;
  }
}

function get_exercise_icon($tag){
  $icons = array(
     "https://rhiaro.co.uk/tags/yoga" => "icon_namaste.png"
    ,"https://rhiaro.co.uk/tags/yin" => "icon_sit.png"
    ,"https://rhiaro.co.uk/tags/hatha" => "icon_tree.png"
    ,"https://rhiaro.co.uk/tags/vinyasa" => "icon_downdog.png"
    ,"https://rhiaro.co.uk/tags/power" => "icon_triangle.png"
  );
  if(isset($icons[$tag])){
    return $icons[$tag];
  }else{
    return false;
  }
}

function get_travel_icon_from_tags($tags){
  if(is_array($tags)){
    foreach($tags as $tag){
      $icon = get_travel_icon($tag);
      if($icon){
        return $icon;
      }
    }
  }
  return get_icon_from_type('as:Travel');
}

function get_icons_from_tags($tags){
  $icons = array();
  foreach($tags as $tag){
    if(get_travel_icon($tag)){
      $icons[$tag] = get_travel_icon($tag);
    }
    if(get_exercise_icon($tag)){
      $icons[$tag] = get_exercise_icon($tag);
    }
  }
  return $icons;
}

function calculate_words_stats($ep, $posts){
  $stats = array("color" => "silver", "width" => "0%", "value" => "unknown");
  $now = new DateTime();
  $from = new DateTime($now->format("Y-m-01"));
  $to = new DateTime($now->format("Y-m-t"));
  $days = $now->format("d"); // this month so far

  $tags = get_tags($ep);
  $poststats = aggregate_writing($posts, $from, $to, $tags);
  $postwords = $poststats["words"];

  $total_words = $postwords;
  $dailywords = ($total_words / $days);

  if($dailywords >= 1667){
    $stats["color"] = "good";
  }elseif($dailywords >= 750){
    $stats["color"] = "med";
  }else{
    $stats["color"] = "bad";
  }

  $monthgoal = 1667 * $now->format("t");
  $percent = $total_words / $monthgoal * 100;
  $stats["width"] = $percent."%";

  $stats["value"] = $total_words;

  return $stats;
}

function calculate_consume_stats($ep){
  $stats = array("color" => "silver", "width" => "0%", "value" => "unknown");
  $obj = construct_last_of_type($ep, "asext:Consume");
  if($obj){
    $now = new DateTime();
    $date = new DateTime(get_value($obj, "as:published"));
    $stats["published"] = $date;
    $stats["content"] = get_value($obj, "as:content");
    $stats["uri"] = key($obj);
    $diff = $date->diff($now);
    if ($diff->y == 0 and $diff->m == 0 and $diff->d == 0 and $diff->h <= 4){
      $stats["color"] = "good";
      $stats["width"] = "100%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d == 0 and $diff->h <= 6){
      $stats["color"] = "good";
      $stats["width"] = "80%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d == 0 and $diff->h <= 8){
      $stats["color"] = "med";
      $stats["width"] = "60%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d == 0 and $diff->h <=12){
      $stats["color"] = "med";
      $stats["width"] = "40%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d == 0 and $diff->h <= 18){
      $stats["color"] = "bad";
      $stats["width"] = "20%";
    }
    else{
      $stats["color"] = "bad";
      $stats["width"] = "1%";
    }
  }
  return $stats;
}

function calculate_budget_stats($ep, $posts){

  $stats = array("color" => "silver", "width" => "0%", "value" => "unknown");
  $now = new DateTime();
  $from = new DateTime($now->format("Y-m-01"));
  $to = new DateTime($now->format("Y-m-t"));

  $tags = get_tags($ep);
  $acquires = aggregate_acquires($posts, $from, $to, $tags);
  $eur = $acquires["totaleur"];

  $percent = round($eur / 1000 * 100);

  $stats["width"] = 100 - $percent."%";
  $monthpercent = round($now->format("d") / $now->format("t") * 100);
  if($percent > $monthpercent){
    $stats["color"] = "bad";
  }elseif($percent == $monthpercent){
    $stats["color"] = "med";
  }else{
    $stats["color"] = "good";
  }

  $acqs = get_type($posts, "asext:Acquire");
  if(!empty($acqs)){
    reset($acqs);
    $latest[key($acqs)] = $acqs[key($acqs)];
    $stats["cost"] = get_value($latest, "asext:cost");
    $stats["content"] = get_value($latest, "as:content");
    $stats["uri"] = key($latest);
    $stats["perc"] = $percent;
  }

  return $stats;
}

function calculate_exercise_stats($ep){
  $q = query_select_last_time_at("https://rhiaro.co.uk/location/exercise");
  $res = execute_query($ep, $q);
  $stats = array("color" => "silver", "width" => "0%", "value" => "unknown");
  if($res){
    $now = new DateTime();
    $date = new DateTime($res["rows"][0]["d"]);
    $stats["published"] = $date;
    $stats["uri"] = $res["rows"][0]["p"];
    $diff = $date->diff($now);
    if ($diff->y == 0 and $diff->m == 0 and $diff->d <= 1){
      $stats["color"] = "good";
      $stats["width"] = "100%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d <= 7){
      $stats["color"] = "good";
      $stats["width"] = "80%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d <= 30){
      $stats["color"] = "med";
      $stats["width"] = "50%";
    }
    elseif ($diff->y == 0 and $diff->m == 0 and $diff->d <= 60){
      $stats["color"] = "bad";
      $stats["width"] = "30%";
    }
    else{
      $stats["color"] = "bad";
      $stats["width"] = "5%";
    }
  }
  return $stats;
}

function stat_box($ep, $type, $posts=null){
  switch($type){
    case "consume":
      $r = calculate_consume_stats($ep);
      break;
    case "exercise":
      $r = calculate_exercise_stats($ep);
      break;
    case "budget":
      $r = calculate_budget_stats($ep, $posts);
      break;
    case "words":
      $r = calculate_words_stats($ep, $posts);
      break;
  }
  return $r;
}

/********************/
/* Data things      */
/********************/

function get_locations($ep){
  $q = query_for_places();
  $r = execute_query($ep, $q);

  if($r){
    $g = new EasyRdf_Graph();
    $g->parse($r, 'php');
    return $g;
  }
  return null;
}

function get_name($ep, $uri){
  $q = query_select_o($uri, "as:name");
  $r = execute_query($ep, $q);
  $name = select_to_list($r);
  if(count($name) > 0){
    return $name[0];
  }
  // todo: deref other uris and look for various name properties
  return str_replace("http://dbpedia.org/resource/", "", $uri);
}

function get_types($ep, $uri){
  global $ns;
  $q = query_select_o($uri, "rdf:type");
  $types = select_to_list(execute_query($ep, $q));
  $shorttypes = array();
  foreach($types as $type){
    $shorttypes[] = $ns->shorten($type);
  }
  return $shorttypes;
}

function get_tags($ep){
  $q = query_select_tags();
  $res = execute_query($ep, $q);
  $tags = array(); $i = 0;
  foreach($res['rows'] as $tag){
    $uri = $tag["tag"];
    if(isset($tag["name"])){
      $tags[$uri]['name'] = $tag["name"];
    }else{
      $tags[$uri]['name'] = $tag["tag"];
    }
    $tags[$uri]['count'] = $tag["c"];
  }
  return $tags;
}

function get_albums($ep){
  $q = query_select_albums();
  $res = execute_query($ep, $q);
  $album_uris = select_to_list($res);
  return $album_uris;
}

function count_items($ep, $collection){
  $total_q = query_count_items($collection);
  $total_res = execute_query($ep, $total_q);
  return $total_res["rows"][0]["c"];
}

function date_from_graph($graph, $subject, $predicate){
  /* Expects a php graph */
  if(!isset($graph[$subject])){
    $graph = array($subject => $graph);
  }
  $date = get_value($graph, $predicate, $subject);
  return new DateTime($date);
}

function published_in_future($graph, $subject){
  /* php graph */
  $date = date_from_graph($graph, $subject, "as:published");
  return date_in_future($date);
}

function date_in_future($date){
  if(!$date instanceof DateTime){
    $date = new DateTime($date);
  }
  $now = new DateTime();
  return $date > $now;
}

function force_present($date, $format=None){
  if(date_in_future($date)){
    $now = new DateTime();
    if(!$format){
      return $now;
    }else{
      return $now->format($format);
    }
  }else{
    return $date;
  }
}

/***********************/
/* Composite things    */
/***********************/

function nav($ep, $resource, $dir="next", $type=0){

  $out = array();

  if(is_array($resource)){
    $uri = get_uri($resource);
  }else{
    $uri = $resource;
  }

  if($type !== 0){

    if(substr($type, 0, 4) == "http"){
      $type = EasyRdf_Namespace::shorten($type);
    }

    if($type != 'as:Activity'){ // Crude but effective.

      if($dir == "next"){
        $q = query_select_s_next_of_type($uri, $type);
      }elseif($dir == "prev"){
        $q = query_select_s_prev_of_type($uri, $type);
      }
    }else{
      return null;
    }

  }else{
    if($dir == "next"){
      $q = query_select_s_next($uri);
    }elseif($dir == "prev"){
      $q = query_select_s_prev($uri);
    }
  }
  $res = execute_query($ep, $q);
  if(!empty($res['rows'])){
    $out[$type] = $res['rows'][0]['s'];
  }

  // TODO: This is a hack until I remove posts that aren't mine from my graph
  if(isset($out[$type]) && substr($out[$type], 0, 21) != "https://rhiaro.co.uk/"){
    return nav($ep, $out[$type], $dir, $type);
  }

  return $out;
}

function post_nav($ep, $ns, $resource){

  $out = array();

  $next = null;
  $prev = null;
  if(get_value($resource, $ns->expand("as:next"))){
    $next = get_value($resource, $ns->expand("as:next"));
  }else{
    $next = nav($ep, $resource, "next");
    if(!empty($next)){
      $next = $next[0];
    }
  }
  if(get_value($resource, $ns->expand("as:prev"))){
    $prev = get_value($resource, $ns->expand("as:prev"));
  }else{
    $prev = nav($ep, $resource, "prev");
    if(!empty($prev)){
      $prev = $prev[0];
    }
  }
  // Get next resource by date of the same type
  $next_types = array();
  $prev_types = array();
  $this_types = get_values($resource, $ns->expand("rdf:type"));

  if(is_array($this_types)){
    foreach($this_types as $type){
      $n = nav($ep, $resource, "next", $type);
      $p = nav($ep, $resource, "prev", $type);
      if($n){
        $next_types = array_merge($next_types, $n);
      }
      if($p){
        $prev_types = array_merge($prev_types, $p);
      }
    }
  }

  $out["next"] = $next;
  $out["prev"] = $prev;
  $out["nexttype"] = $next_types;
  $out["prevtype"] = $prev_types;

  return $out;
}

function construct_collection_page($ep, $collection, $before=null, $limit=16, $sort="as:published", $from_graph="https://blog.rhiaro.co.uk/"){

  $total = count_items($ep, $collection);

  if(!isset($before)){
    $qlimit = $limit+1;
  }else{
    $qlimit = $limit;
  }

  $items_q = query_select_prev_items($collection, $before, $sort, $qlimit);

  $item_uris = select_to_list(execute_query($ep, $items_q));
  if(isset($before)){
    array_unshift($item_uris, $before);
    $next_q = query_select_next_items($collection, $before, "as:published", $limit);
    $next_uris = select_to_list(execute_query($ep, $next_q));
    if(count($next_uris) > 0){
      $nextstart = $next_uris[count($next_uris)-1];
      $next = $collection . "?before=" . $nextstart . "&limit=" . $limit;
    }
  }

  if(count($item_uris) > $limit){
    $prevstart = array_pop($item_uris);
    $prev = $collection . "?before=" . $prevstart . "&limit=" . $limit;
  }

  $page_uri = $collection."?before=".$item_uris[0]."&limit=".$limit;
  $page_q = query_construct_collection_page($page_uri, $collection);
  $page_res = execute_query($ep, $page_q);

  $page = new EasyRdf_Graph($page_uri);
  $page->parse($page_res, 'php');
  if(isset($prev)){
    $page->addResource($page_uri, "as:prev", $prev);
  }
  if(isset($next)){
    $page->addResource($page_uri, "as:next", $next);
  }

  $totalItems = new EasyRdf_Literal($total, null, "xsd:nonNegativeInteger");
  $page->add($collection, "as:totalItems", $totalItems);
  $page->addResource($collection, "rdf:type", "as:Collection");

  $items = construct_uris_in_graph($ep, $item_uris, $from_graph);
  $items_g = new EasyRdf_Graph();
  $items_g->parse($items, 'php');
  foreach($item_uris as $item){
    $page->addResource($page_uri, "as:items", $item);
  }
  $final = merge_graphs(array($page, $items_g), $page_uri);

  return $final;
}

function make_collection_page($ep, $uri, $item_uris, $nav, $before=null, $limit=16, $sort="as:published", $from_graph="https://blog.rhiaro.co.uk/"){

  $uri = drop_collection_page_params($uri);

  if(!isset($before)){
    $qlimit = $limit+1;
  }else{
    $qlimit = $limit;
  }

  if(isset($before)){
    array_unshift($item_uris, $before);

    if(isset($nav["next"])){
      $next = $uri . "?before=" . $nav["next"] . "&limit=" . $limit;
    }
  }

  if(isset($nav["prev"])){
    $prev = $uri . "?before=" . $nav["prev"] . "&limit=" . $limit;
  }

  if(is_array($item_uris) && count($item_uris) > 0){
    $page_uri = $uri."?before=".$item_uris[0]."&limit=".$limit;
  }else{
    $page_uri = $uri;
  }
  $page_q = query_construct_collection_page($page_uri, $uri);
  $page_res = execute_query($ep, $page_q);

  $page = new EasyRdf_Graph($page_uri);
  $page->parse($page_res, 'php');
  if(isset($prev)){
    $page->addResource($page_uri, "as:prev", $prev);
  }
  if(isset($next)){
    $page->addResource($page_uri, "as:next", $next);
  }

  // $page->addLiteral($collection, "as:totalItems", $total);
  $page->addResource($uri, "rdf:type", "as:Collection");
  $page->addResource($page_uri, "rdf:type", "as:CollectionPage");

  $items = construct_uris_in_graph($ep, $item_uris, $from_graph);
  $items_g = new EasyRdf_Graph();

  $items_g->parse($items, 'php');
  foreach($item_uris as $item){
    $page->addResource($page_uri, "as:items", $item);
    $page->addResource($uri, "as:items", $item);
  }

  $final = merge_graphs(array($page, $items_g), $page_uri);
  return $final;
}

function make_checkin_summary($checkin, $locations=null, $end=null){

  global $ep;
  $summary = array();

  $location = get_value($checkin, "as:location");
  if($locations === null){
    $locations = get_locations($ep);
  }
  if(isset($locations[$location])){
    $location = array($location => $locations[$location]);
  }else{
    $location = array($location=>array());
  }

  $pub = new DateTime(get_value($checkin, "as:published"));
  if($end === null){
    $end = new DateTime();
    $location_label = get_value($location, "blog:presentLabel");
    $end_label = "now";
  }else{
    $end = new DateTime($end);
    $location_label = get_value($location, "blog:pastLabel");
    $end_label = $end->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F");
  }

  $diff = time_diff_to_human($pub, $end);
  if(empty($location_label)){
    $location_label = "was last spotted at ".key($location);
  }

  $summary["location"] = $location_label;
  $summary["location_uri"] = key($location);
  $summary["for"] = $diff;
  $summary["from"] = $pub;
  $summary["to"] = $end;

  $label = "rhiaro ".$location_label." for ".$diff." (from ".$pub->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")." until ".$end_label.")";
  $summary["string"] = $label;

  return $summary;
}

function nanowrimo_total($ep, $year=null){
  if($year == null){
    $now = new DateTime();
    $year = $now->format("Y");
  }
  if($year == "2018"){ $month = "10"; }
  else{ $month = "11"; }
  $from = $year."-".$month."-01T00:00:00";
  $to = $year."-".$month."-30T23:59:59";
  $q = query_select_wordcount($from, $to);
  $r = execute_query($ep, $q);
  $total = 0;
  foreach($r["rows"] as $res){
    $total = $total + $res['wc'];
  }
  return number_format($total, 0, ".", ",");
}

/***********************/
/* Helpers             */
/***********************/

function time_ago($date, $round=false){
  $now = new DateTime();
  return time_diff_to_human($date, $now, $round)." ago";
}

function time_diff_to_human($date, $date2, $round=false){
  if(gettype($date) == "string" || get_class($date) != "DateTime"){ $date = new DateTime($date); }
  if(gettype($date2) == "string" || get_class($date2) != "DateTime"){ $date2 = new DateTime($date2); }

  $duration = $date->diff($date2);
  $y = $duration->y;
  $m = $duration->m;
  $d = $duration->d;
  $h = $duration->h;
  $i = $duration->i;
  $s = $duration->s;

  if($round == "years" && $y == 0 && $m < 6){
    $round = "months";
  }
  if($round == "months" && $m == 0 && $d < 15){
    $round = "days";
  }
  if($round == "days" && $d == 0 && $h < 12){
    $round = "hours";
  }
  if($round == "hours" && $h == 0 && $i < 30){
    $round = "minutes";
  }
  if($round == "minutes" && $i == 0 && $s < 30){
    $round = "seconds";
  }

  if($round == "years"){
    if($m >= 6){
      $y = $y + 1;
    }
    $m = $d = $h = $i = $s = 0;
  }elseif($round == "months"){
    if($d >= 15){
      $m = $m + 1;
    }
    $d = $h = $i = $s = 0;
  }elseif($round == "days"){
    if($h >= 12){
      $d = $d + 1;
    }
    $h = $i = $s = 0;
  }elseif($round == "hours"){
    if($i >= 30){
      $h = $h + 1;
    }
    $i = $s = 0;
  }elseif($round == "minutes"){
    if($s >= 30){
      $i = $i + 1;
    }
    $s = 0;
  }

  $ago = array();
  if($y > 0){
    $str = $y . " year";
    if($y > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if($m > 0){
    $str = $m . " month";
    if($m > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if($d > 0){
    $str = $d . " day";
    if($d > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if($h > 0){
    $str = $h . " hour";
    if($h > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if($i > 0){
    $str = $i . " minute";
    if($i > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if($s > 0){
    $str = $s . " second";
    if($s > 1){ $str .=  "s"; }
    $ago[] = $str;
  }
  if(count($ago) == 1){
    $out = $ago[0];
  }elseif(count($ago) > 1){
    $ago[count($ago)-1] = " and ".$ago[count($ago)-1];
    $out = implode(", ", $ago);
  }else{
    $out = "less than a second";
  }

  return $out;
}

function lat_lon_to_map($lat, $lon, $zoom=8){
  $x = floor((($lon + 180) / 360) * pow(2, $zoom));
  $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
  //$map = "http://b.tile.openstreetmap.org/$zoom/$x/$y.png";
  $map = "http://a.basemaps.cartocdn.com/light_all/$zoom/$x/$y.png";
  return $map;
}
function map_path($start, $end){
  $map = "https://atlas.p3k.io/map/img?basemap=stamen_terrain&attribution=none&width=800&height=240&path[]=[".$start[0].",".$start[1]."],[".$end[0].",".$end[1]."]&bezier=25";
  return $map;
}

function next_tile_x($tile){
  $url = explode("/", $tile);
  $xpos = count($url) - 2;
  $url[$xpos] = $url[$xpos] + 1;
  return implode("/", $url);
}
function prev_tile_x($tile){
  $url = explode("/", $tile);
  $xpos = count($url) - 2;
  $url[$xpos] = $url[$xpos] - 1;
  return implode("/", $url);
}

function structure_cost($cost, $currencies_file="currencies.json"){
  // This is terrible.
  // Accounting for messy human input.
  // Replace this with retreiving the currency code from however you store the cost of something.
  $cheat = array("&pound;" => "GBP", "&dollar;" => "USD", "$" => "USD", "£" => "GBP", "QR" => "QAR", "&euro;" => "EUR");
  foreach($cheat as $s => $c){
    if(stripos($cost, $s) !== false){
      $cur = $cheat[$s];
      $amt = str_replace($s, "", $cost);
      break;
    }else{
      $cur = $cost;
      $amt = $cost;
    }
  }
  $amt = str_replace(",", "", $amt);
  $cur = str_replace(",", "", $cur);
  $amt = floatval($amt);
  $cur = str_replace($amt, "", $cur);
  $cur = trim(str_replace("0", "", $cur));
  $currencies = json_decode(file_get_contents($currencies_file), true);
  $currencies = $currencies['results'];
  if(array_key_exists(strtoupper($cur), $currencies)){
    $code = strtoupper($cur);
  }else{
    $code = null;
  }
  return array("currency" => $code, "value" => $amt);
}

function drop_collection_page_params($uri){
  $parsed = parse_url($uri);
  if(isset($parsed["query"])) {
    parse_str($parsed["query"], $params);
    if(isset($params["before"])){ unset($params["before"]); }
    if(isset($params["limit"])){ unset($params["limit"]); }
    if(!empty($params)){
      $parsed["query"] = http_build_query($params);
    }else{
      unset($parsed["query"]);
    }
  }
  return unparse_url($parsed);
}

function unparse_url($parsed_url) {
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
  $pass     = ($user || $pass) ? "$pass@" : '';
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  return "$scheme$user$pass$host$port$path$query$fragment";
}

/*****************/
/* Scoring       */
/*****************/

function score_predicates(){
  return array(
      'view:banality'
    , 'view:intimacy'
    , 'view:tastiness'
    , 'view:wanderlust'
    , 'view:informative'
  );
}

function set_views($ep, $resource){

  if(!$resource->get($resource, 'view:css')){
    $resource->addLiteral('view:stylesheet', 'views/'.get_style($resource).".css");
  }

  // Background colour for places and checkins
  if($resource->get('view:color') && !$resource->get('view:css')){
    $resource->addLiteral('view:css', "h1 { color: ".$resource->get('view:color')."; }\n");
  }
  if($resource->isA('as:Arrive')){
    $loc = get($ep, $resource->get('as:location'));
    $loc = $loc['content'];
    if($loc){
      $resource->addLiteral('view:css', ".btn { background-color: ".$loc->get($resource->get('as:location'), 'view:color')."; }\n");
    }
  }
  return $resource;
}

function get_style($resource){

  $score = array();
  $scorepreds = score_predicates();

  foreach($scorepreds as $p){
    if($resource->hasProperty($p)){
      $score[$p] = $resource->get($p)->getValue();
    }
  }

// $food = array(5,3,5,0,0);
// $lyric = array(5,5,0,0,0);
// $wg = array(0,0,0,0,4);
// $phd = array(0,1,0,0,5);
// $trek = array(4,4,0,0,0);
// $checkin = array(5,3,0,4,0);
// $feels = array(0,5,0,0,1);

  // array ( name of stylesheet => minimum scores required to trigger )
  $styles = array(
       "base" => array()
      ,"banal" => array("view:banality" => 3)
      ,"intimate" => array("view:intimacy" => 3)
      ,"tasty" => array("view:tastiness" => 5)
      ,"wander" => array("view:wanderlust" => 3)
      ,"scholar" => array("view:informative" => 4) // this is acm
      ,"checkin" => array("view:wanderlust" => 4, "view:banality" => 5, "view:intimacy" => 5)
      ,"travel" => array("view:wanderlust" => 5, "view:banality" => 3, "view:intimacy" => 5)
    );

  $s = array();
  foreach($styles as $name => $numbers){

    if($numbers == $score){
      return $name;
    }else{
      foreach($numbers as $pred => $val){
        if(isset($score[$pred])){
          if($score[$pred] >= $val){
            $s[$name] = $styles[$name];
          }else{
            unset($s[$name]);
            break;
          }
        }
      }

    }
  }
  arsort($s);
  return key($s);
}
?>