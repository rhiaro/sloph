<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$uri = "https://rhiaro.co.uk".$relUri;
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$tags = get_tags($ep);

$y = $nexty = $_GET['y'];
$prevy = $y-1;
if(isset($_GET['m'])){
    $m = $_GET['m'];
    $nextm = str_pad($m + 1, 2, '0', STR_PAD_LEFT);

    if($nextm == '13'){
        $nextm = '01';
        $nexty = $y + 1;
    }

    $next_uri = "https://rhiaro.co.uk/$nexty/$nextm/";
    $prevm = str_pad($m-1, 2, '0', STR_PAD_LEFT);
    if($prevm == '00'){
        $prevm = '12';
        $prevy = $y-1;
    }else{
        $prevy = $y;
    }
    $prev_uri = "https://rhiaro.co.uk/$prevy/$prevm/";

}else{
    $m = $nextm = '01';
    $nexty = $y + 1;

    $next_uri = "https://rhiaro.co.uk/$nexty/";
    $prev_uri = "https://rhiaro.co.uk/$prevy/";
}

$after = new DateTime("$y-$m-01T00:00:00+00:00");
$before = new DateTime("$nexty-$nextm-01T00:00:00+00:00");
$after = $after->format(DateTime::ATOM);
$before = $before->format(DateTime::ATOM);
$in_feed = true;

$typesar = array(
    "stuff" => array("asext:Acquire"),
    "articles" => array("as:Article"),
    "arrives" => array("as:Arrive"),
    "where" => array("as:Arrive"),
    "eats" => array("asext:Consume"),
    "notes" => array("as:Note"),
    "words" => array("asext:Write"),
    "photos" => array("as:Add"),
    "adds" => array("as:Add")
);

if(isset($_GET['t']) && array_key_exists($_GET['t'], $typesar)){
    $types = $typesar[$_GET['t']];
}else{
    $types = ["as:Article", "as:Note", "as:Add"];
}
$q = query_select_s_between_types($after, $before, $types, "https://blog.rhiaro.co.uk/");
// var_dump(htmlentities($q));
$item_uris = select_to_list_sorted(execute_query($ep, $q), 'd');

$name = "Posts between $y/$m and $nexty/$nextm";
$nav = array("next" => $next_uri, "prev" => $prev_uri);

$g = get_container_dynamic_from_items($ep, $uri, 'as:published', $name, $item_uris, count($item_uris), $nav, true);

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