<?

function get_icon($resource){
  foreach($resource->types() as $type){
    if($resource->isA("as:Add") && $resource->get("as:target") == "https://rhiaro.co.uk/bookmarks/"){
      $t = "&#128278;";
    }else{
      $t = get_icon_from_type($type);
    }
    if($t) { return $t; }
  }
}

function get_icon_from_type($type){
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
  if(isset($icons[$type])){
    return $icons[$type];
  }else{
    return false;
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

function get_travel_icon_from_tags($tags){
  foreach($tags as $tag){
    if(get_class($tag) == "EasyRdf_Resource"){
      $icon = get_travel_icon($tag->getUri());
      if($icon){
        return $icon;
      }
    }
  }
  return get_icon_from_type('as:Travel');
}

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
    return $resource->get($uri, 'as:name');
  }else{
    // todo: deref other uris and look for various name properties
    return str_replace("http://dbpedia.org/resource/", "", $uri);
  }
}

function time_ago($date){
  if(gettype($date) != "DateTime"){
    $date = new DateTime($date);
  }
  $duration = $date->diff(new DateTime());
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
  $ago[count($ago)-1] = " and ".$ago[count($ago)-1] . " ago";
  return implode(", ", $ago);
}

function lat_lon_to_map($lat, $lon, $zoom=8){
  $x = floor((($lon + 180) / 360) * pow(2, $zoom));
  $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
  //$map = "http://b.tile.openstreetmap.org/$zoom/$x/$y.png";
  $map = "http://a.basemaps.cartocdn.com/light_all/$zoom/$x/$y.png";
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

/* Scoring */

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
  
  if(!$resource->get('view:css')){
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
      ,"scholar" => array("view:informative" => 3)
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

function view_router($resource){

    if($resource->isA("as:Add") || $resource->isA("as:Like") || $resource->isA("as:Announce") || $resource->isA("as:Follow")){
      return 'link';
    }elseif($resource->isA("as:Arrive")){ 
      return 'checkin';
    }elseif($resource->isA("as:Travel") && $resource->get('as:origin') && $resource->get('as:target')){
      return 'travel';
    }elseif($resource->isA("asext:Consume") || $resource->isA("asext:Acquire")){
      return 'stuff';
    }elseif($resource->isA("as:Invite") || $resource->isA("as:Accept") || $resource->isA("as:Event")){
      return 'event';
    }elseif($resource->isA("as:Collection")){
      return 'collection';
    }else{
      return 'article';
    }
}
?>