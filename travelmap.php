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
$graph->add($travelmap_uri, "view:stylesheet", "views/sitrep/sitrep.css");

$now = new DateTime();
$start = new DateTime("2004-01-01");

$res = get_travels($ep);
$places = get_places($ep);
$posts = time_in_places($res);
$data = generate_map_data($posts, $places);

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

    $external_styles = array("https://unpkg.com/leaflet@1.6.0/dist/leaflet.css");

    include 'views/top.php';
    include 'views/header_stats.php';
    include 'views/nav_header.php';
?>

    <main class="w1of1">

      <div id="themap"></div>

      <article id="tablewrapper">
      <table style="margin-left: auto; margin-right: auto;">
        <thead>
          <tr>
            <th class="sort" data-sort="where">Where</th>
            <th class="sort" data-sort="when">When</th>
            <th class="sort" data-sort="until">Until</th>
            <th class="sort" data-sort="for">For</th>
          </tr>
        </thead>
        <tbody class="list" id="thetable"></tbody>
      </table>
    </article>
    </main>

    <script>
      data = <?=$data?>;
    </script>

<?
    $scripts = array("https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
                    ,"https://stamen-maps.a.ssl.fastly.net/js/tile.stamen.js"
                    ,"https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"
                    ,"views/sitrep/svg-icon.js"
                    ,"views/sitrep/sitrep.js"
                );
    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>