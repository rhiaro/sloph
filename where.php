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


$where = get_resource($ep, $item_uris[0]);
// Temporary for checkins
$where->addLiteral($uri, 'view:banality', 5);
$where->addLiteral($uri, 'view:intimacy', 5);
$where->addLiteral($uri, 'view:wanderlust', 4);
$summary = make_checkin_summary($where->toRdfPhp(), $locations);
$where->addLiteral($uri, 'as:summary', $summary["string"]);

$g = $where;

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