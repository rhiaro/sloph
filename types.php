<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$uri = "https://rhiaro.co.uk".$relUri;
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$typemap = array("checkins" => "as:Arrive"
                ,"arrives" => "as:Arrive"
                ,"consumes" => "asext:Consume"
                ,"eats" => "asext:Consume"
                ,"acquires" => "asext:Acquire"
                ,"stuff" => "asext:Acquire"
                ,"likes" => "as:Like"
                ,"events" => "as:Event"
                ,"bookmarks" => "as:Add"
                ,"adds" => "as:Add"
                ,"collections" => "as:Add"
                ,"reposts" => "as:Announce"
                ,"rsvps" => "as:Accept"
                ,"articles" => "as:Article"
                ,"writes" => "as:Article"
                ,"notes" => "as:Note"
                ,"places" => "as:Place"
                ,"follows" => "as:Follow"
                ,"where" => "as:Arrive"
                ,"words" => "asext:Write"
  );

if(!isset($_GET['type']) || !array_key_exists($_GET['type'], $typemap)){
  header("HTTP/1.1 404 Not Found");
  echo $_GET['type']."<br/>";
  echo "HTTP 404 Not Found. How about <a href=\"https://rhiaro.co.uk/writes/\">/writes</a>?";
  die();
}

$locations = get_locations($ep);
$locations = $locations->toRdfPhp();
$tags = get_tags($ep);
$in_feed = true;

if($_GET['type'] == "places"){
  $sort = "as:name";
}else{
  $sort = "as:published";
}

if(isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] < 64){
  $limit = $_GET['limit'];
}else{
  $limit = 16;
}

$qc = query_count_type($typemap[$_GET['type']]);
$resc = execute_query($ep, $qc);
$total = $resc["rows"][0]["c"];

$next_uri = null;
if(isset($_GET['before'])){
  $q = query_select_prev_type($typemap[$_GET['type']], $_GET['before'], $sort, $limit, "https://blog.rhiaro.co.uk/");
  
  $next_q = query_select_next_type($typemap[$_GET['type']], $_GET['before'], $sort, $limit, "https://blog.rhiaro.co.uk/");
  $next_uris = select_to_list(execute_query($ep, $next_q));
  if(count($next_uris) > 0){
    $next_uri = $next_uris[count($next_uris)-1];
  }
}else{
  $q = query_select_s_type($typemap[$_GET['type']], $sort, "DESC", $limit+1, "https://blog.rhiaro.co.uk/");
}

if($_GET["type"] == "bookmarks"){
  $vals = array("as:published" => "?published", "rdf:type" => "as:Add", "as:target" => "<https://rhiaro.co.uk/bookmarks/>");
  $q = query_select_s_where($vals, 0, "published");
}

$item_uris = select_to_list(execute_query($ep, $q));
$prev_uri = array_pop($item_uris);
$name = ucfirst($_GET['type']);
$nav_prep = array("next" => $next_uri, "prev" => $prev_uri);

if($_GET['type'] == "where"){
  // TODO: move this somewhere else
  $where = get_resource($ep, $item_uris[0]);
  // Temporary for checkins
  $where->addLiteral($uri, 'view:banality', 5);
  $where->addLiteral($uri, 'view:intimacy', 5);
  $where->addLiteral($uri, 'view:wanderlust', 4);
  $summary = make_checkin_summary($where->toRdfPhp(), $locations);
  $where->addLiteral($uri, 'as:summary', $summary["string"]);

  $g = $where;
}else{
  $g = get_container_dynamic_from_items($ep, $uri, $sort, $name, $item_uris, $total, $nav_prep);
}

$result = conneg($acceptheaders, $g);
$content = $result['content'];
$header = $result['header'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $styled = set_views($ep, $content->resource($content->getUri()));
    $resource = merge_graphs(array(new EasyRdf_Graph($styled), $content), $content->getUri());
    $resource = $resource->toRdfPhp();

    include 'views/top.php';
    include 'views/nav.php';
    include 'views/'.view_router($resource).'.php';
    include 'views/nav.php';
    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>