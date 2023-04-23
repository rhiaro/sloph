<?
require_once('../../vendor/init.php');
$q = get_prefixes();
$q .= "SELECT * WHERE {
  ?uri a as:Travel .
  ?uri as:tag <https://rhiaro.co.uk/tags/finland23> .
  ?uri as:origin ?start .
  ?uri as:target ?end .
  ?uri as:startTime ?starttime .
  OPTIONAL { ?uri as:endTime ?endtime . }
  ?uri as:summary ?summary .
  ?start a as:Place .
  ?start as:name ?startname .
  ?start as:latitude ?startlat .
  ?start as:longitude ?startlon .
  ?end a as:Place .
  ?end as:name ?endname .
  ?end as:latitude ?endlat .
  ?end as:longitude ?endlon .
}";
$r = execute_query($ep, $q);

$data = array();
foreach($r["rows"] as $row){
  $data[$row["uri"]] = array(
                          "from" => array("name"=>$row["startname"],
                                          "lat"=>$row["startlat"],
                                          "lon"=>$row["startlon"],
                                          "date"=>$row["starttime"]),
                          "to" => array("name"=>$row["endname"],
                                          "lat"=>$row["endlat"],
                                          "lon"=>$row["endlon"],
                                          "date"=>$row["endtime"]),
                          "text" => $row["summary"]
                            );

}

?>

<!doctype html>
<html>
  <head>
    <title>journey map</title>
    <link rel="stylesheet" href="https://rhiaro.co.uk/views/normalize.min.css" />
    <link rel="stylesheet" href="https://rhiaro.co.uk/views/base.css" />
    <link rel="stylesheet" href="https://rhiaro.co.uk/views/core.css" />
    <link rel="stylesheet" href="https://rhiaro.co.uk/views/sitrep/sitrep.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" />
  </head>
  <body>
    <main class="wrapper">
      <div id="themap" style="display:none"></div>
      <article>
        <ul>
          <?foreach($data as $uri => $journey):?>
            <li><a href="<?=$uri?>"><?=$journey["text"]?></a></li>
          <?endforeach?>
        </ul>
      </article>
    </main>
    <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"></script>
    <script src="https://rhiaro.co.uk/views/sitrep/svg-icon.js"></script>
    <script>
      document.getElementById("themap").style.display = "block";
      // document.getElementById("noscript").style.display = "none";
      
      // Map

      var layer = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
      });
      var map = new L.Map("themap", {
          center: new L.LatLng(61.59359, 10.986328),
          zoom: 4
      });
      map.addLayer(layer);
    </script>
  </body>
</html>