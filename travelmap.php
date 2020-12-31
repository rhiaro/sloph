<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$base = "https://rhiaro.co.uk";

$travelmap_uri = "https://rhiaro.co.uk/map";
$graph = new EasyRdf_Graph($travelmap_uri);
$graph->addType($travelmap_uri, "as:Collection");
$graph->add($travelmap_uri, "as:name", "Travel log");
$graph->add($travelmap_uri, "as:summary", "All the places I have travelled");

$now = new DateTime();
$start = new DateTime("2004-01-01");

$res = get_travels($ep);
$posts = time_in_places($res);
// var_dump($posts);

$result = conneg($acceptheaders, $graph);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $resource = $graph->resource($travelmap_uri);

    require_once('vendor/sloph/header_stats.php');

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';
    include 'views/header_stats.php';
    include 'views/nav_header.php';
?>

    <main class="wrapper w1of1">

      <div id="archive">
      <?foreach($posts as $place => $post):?>
        <p>In <?=$place?>:</p>
        <ul>
          <?foreach($post["visits"] as $visit):?>
            <li><?=$visit["startDate"]?> to <?=$visit["endDate"]?></li>
          <?endforeach?>
        </ul>
      <?endforeach?>
      </div>
      <nav><p><a href="#top">top</a></p></nav>
    </main>

<?
    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>