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
                ,"reposts" => "as:Announce"
                ,"rsvps" => "as:Accept"
                ,"articles" => "as:Article"
                ,"writes" => "as:Article"
                ,"notes" => "as:Note"
                ,"places" => "as:Place"
                ,"follows" => "as:Follow"
                ,"where" => "as:Arrive"
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

if($_GET['type'] == "places"){
  $sort = "as:name";
}else{
  $sort = "as:published";
}

$qc = query_count_type($typemap[$_GET['type']]);
$resc = execute_query($ep, $qc);
$total = $resc["rows"][0]["c"];

if(isset($_GET['before'])){
  $q = query_select_prev_type($typemap[$_GET['type']], $_GET['before'], $sort, 16, "https://blog.rhiaro.co.uk/");
}else{
  $q = query_select_s_type($typemap[$_GET['type']], $sort, "DESC", 17, "https://blog.rhiaro.co.uk/");
}
$item_uris = select_to_list(execute_query($ep, $q));
$prev_uri = array_pop($item_uris);

$name = ucfirst($_GET['type']);
$nav = array("next" => "next", "prev" => $prev_uri); // TODO HERENOW

if($_GET['type'] == "where"){
  // TODO: move this somewhere else
  $where = get_resource($ep, $item_uris[0]);
  // Temporary for checkins
  $g->addLiteral($uri, 'view:banality', 5);
  $g->addLiteral($uri, 'view:intimacy', 5);
  $g->addLiteral($uri, 'view:wanderlust', 4);
  $summary = make_checkin_summary($where, $locations);
  $g->addLiteral($uri, 'as:summary', $summary);
}else{
  $g = get_container_dynamic_from_items($ep, $uri, $sort, $name, $item_uris, $total, $nav);
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
    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>