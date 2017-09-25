<?

function view_router($resource){

    if(has_type($resource, "as:Add") || has_type($resource, "as:Like") || has_type($resource, "as:Announce") || has_type($resource, "as:Follow")){
      if(has_type($resource, "as:Add") && get_value($resource, 'as:target') != "https://rhiaro.co.uk/bookmarks/"){
        // TODO: check if target is an album instead of just excluding bookmarks
        return 'objects';
      } 
      return 'link';
    }elseif(has_type($resource, "as:Arrive")){ 
      return 'checkin';
    }elseif(has_type($resource, "as:Travel") && get_value($resource, 'as:origin') && get_value($resource, 'as:target')){
      return 'travel';
    }elseif(has_type($resource, "asext:Consume") || has_type($resource, "asext:Acquire")){
      return 'stuff';
    }elseif(has_type($resource, "as:Invite") || has_type($resource, "as:Accept") || has_type($resource, "as:Event")){
      return 'event';
    }elseif(has_type($resource, "as:Collection") || has_type($resource, "as:CollectionPage")){
      return 'collection';
    }else{
      return 'article';
    }
}

/**********************/
/* Visual things      */
/**********************/

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
  foreach($tags as $tag){
    $icon = get_travel_icon($tag);
    if($icon){
      return $icon;
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

  $resource = get($ep, $uri);
  if($resource['content']){
    $r = $resource['content']->toRdfPhp();
    $name = get_value($r, 'as:name');
    if(!empty($name)){
      return $name;
    }
  }
  // todo: deref other uris and look for various name properties
  return str_replace("http://dbpedia.org/resource/", "", $uri);
}

function get_tags($ep){
  $q = query_select_tags();
  $res = execute_query($ep, $q);
  // var_dump($res);
  $tags = array(); $i = 0;
  foreach($res['rows'] as $tag){
    if($tag["tag type"] == "uri"){
      $uri = $tag["tag"];
      if(isset($tag["name"])){
        $tags[$uri]['name'] = $tag["name"];
      }else{
        $tags[$uri]['name'] = $tag["tag"];
      }
      $tags[$uri]['count'] = $tag["c"];
    }
  }
  return $tags;
}

function count_items($ep, $collection){
  $total_q = query_count_items($collection);
  $total_res = execute_query($ep, $total_q);
  return $total_res["rows"][0]["c"];
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

function construct_collection_page($ep, $collection, $before=null, $limit=16, $sort="as:published"){

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

  $page->addLiteral($collection, "as:totalItems", $total);
  $page->addResource($collection, "rdf:type", "as:Collection");

  $items = construct_uris_in_graph($ep, $item_uris, "https://blog.rhiaro.co.uk/");
  $items_g = new EasyRdf_Graph();
  $items_g->parse($items, 'php');
  foreach($item_uris as $item){
    $page->addResource($page_uri, "as:items", $item);
  }
  $final = merge_graphs(array($page, $items_g), $page_uri);
  return $final;
}

function make_collection_page($ep, $uri, $item_uris, $nav, $before=null, $limit=16, $sort="as:published"){

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
  
  $page_uri = $uri."?before=".$item_uris[0]."&limit=".$limit;
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

  $items = construct_uris_in_graph($ep, $item_uris, "https://blog.rhiaro.co.uk/");
  $items_g = new EasyRdf_Graph();
  $items_g->parse($items, 'php');
  foreach($item_uris as $item){
    $page->addResource($page_uri, "as:items", $item);
  }
  $final = merge_graphs(array($page, $items_g), $page_uri);
  return $final;
}

function make_checkin_summary($checkin, $locations=null, $end=null){
  
  $location = get_value($checkin, "as:location");
  if($locations === null){
    $locations = get_locations();
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

  $label = "rhiaro ".$location_label." for ".$diff." (from ".$pub->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")." until ".$end_label.")";
  return $label;
}

/***********************/
/* Helpers             */
/***********************/

function time_ago($date){
  $now = new DateTime();
  return time_diff_to_human($date, $now)." ago";
}

function time_diff_to_human($date, $date2){
  if(gettype($date) == "string" || get_class($date) != "DateTime"){ $date = new DateTime($date); }
  if(gettype($date2) == "string" || get_class($date2) != "DateTime"){ $date2 = new DateTime($date2); }
  $duration = $date->diff($date2);
  
  $ago = array();
  if($duration->y > 0){ 
    $y = $duration->y . " year"; 
    if($duration->y > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  if($duration->m > 0){ 
    $y = $duration->m . " month"; 
    if($duration->m > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  if($duration->d > 0){ 
    $y = $duration->d . " day"; 
    if($duration->d > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  if($duration->h > 0){ 
    $y = $duration->h . " hour"; 
    if($duration->h > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  if($duration->i > 0){ 
    $y = $duration->i . " minute"; 
    if($duration->i > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  if($duration->s > 0){ 
    $y = $duration->s . " second"; 
    if($duration->s > 1){ $y .=  "s"; }
    $ago[] = $y;
  }
  $ago[count($ago)-1] = " and ".$ago[count($ago)-1];
  return implode(", ", $ago);
}

function lat_lon_to_map($lat, $lon, $zoom=8){
  $x = floor((($lon + 180) / 360) * pow(2, $zoom));
  $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
  //$map = "http://b.tile.openstreetmap.org/$zoom/$x/$y.png";
  $map = "http://a.basemaps.cartocdn.com/light_all/$zoom/$x/$y.png";
  return $map;
}
function map_path($start, $end){
  $map = "https://atlas.p3k.io/map/img?basemap=gray&width=800&height=240&path[]=[".$start[0].",".$start[1]."],[".$end[0].",".$end[1]."]&bezier=25";
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

function structure_cost($cost){
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
  $currencies = json_decode(file_get_contents('currencies.json'), true);
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
  parse_str($parsed["query"], $params);
  if(isset($params["before"])){ unset($params["before"]); }
  if(isset($params["limit"])){ unset($params["limit"]); }
  if(!empty($params)){
    $parsed["query"] = http_build_query($params);
  }else{
    unset($parsed["query"]);
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
    $resource->addLiteral('view:css', "body { background-color: ".$resource->get('view:color')."; }\n");
  }
  if($resource->isA('as:Arrive')){
    $loc = get($ep, $resource->get('as:location'));
    $loc = $loc['content'];
    if($loc){
      $resource->addLiteral('view:css', "body { background-color: ".$loc->get($resource->get('as:location'), 'view:color')."; }\n");  
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