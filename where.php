<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$uri = "https://rhiaro.co.uk".$relUri;
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$locations = get_locations($ep);
$locations = $locations->toRdfPhp();
$tags = get_tags($ep);

$q = query_select_s_type("as:Arrive", "as:published", "DESC", "17", "https://blog.rhiaro.co.uk/");

$item_uris = select_to_list(execute_query($ep, $q));
$next_uri = null;
$prev_uri = array_pop($item_uris);
$name = "where is rhiaro";
$nav_prep = array("next" => $next_uri, "prev" => $prev_uri);

$where_uri = $item_uris[0];
$where = get_resource($ep, $where_uri);
// Temporary for checkins
$where->addLiteral($where_uri, 'view:banality', 5);
$where->addLiteral($where_uri, 'view:intimacy', 5);
$where->addLiteral($where_uri, 'view:wanderlust', 4);
$summary = make_checkin_summary($where->toRdfPhp(), $locations);
$where->addLiteral($where_uri, 'as:summary', $summary["string"]);

$g = $where;

$home_locations = array("https://rhiaro.co.uk/location/meeting", "https://rhiaro.co.uk/location/home");
$q2 = query_select_last_time_not_at($home_locations);
$r2 = execute_query($ep, $q2);
if($r2){
    $nothome = $r2["rows"][0]["s"];
    $q3 = query_select_s_next_of_type($nothome, "as:Arrive");
    $r3 = execute_query($ep, $q3);
    if($r3["rows"]){
        $now = new DateTime();
        $date = new DateTime($r3["rows"][0]["d"]);
        $homefor = time_diff_to_human($date, $now);
    }
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

    $includes = array(view_router($resource).'.php');
    include 'views/page_template.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>