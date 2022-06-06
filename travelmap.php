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
$graph->add($travelmap_uri, "view:stylesheet", "views/base.css");
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

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    $includes = array('listing_travel.php');
    $external_styles = array("https://unpkg.com/leaflet@1.6.0/dist/leaflet.css");
    $scripts = array("https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
                    ,"views/listjs/list.min.js"
                    ,"views/sitrep/svg-icon.js"
                    ,"views/sitrep/sitrep.js"
                );
    include 'views/page_template.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>