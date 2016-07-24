<?
require_once('../vendor/init.php');

function write($data, $fn){
  $base = $_SERVER["HTTP_HOST"];
  if(!$slug) $slug = uniqid();
  if(!$date) $date = date("ymd-His");
    
  $log = "data/$fn.ttl";

  $h = fopen($log, 'w');
  fwrite($h, $data);
  fclose($h);
  return $log;
}

$posts = array();

$q = query_select_s();
$r = execute_query($ep, $q);
if($r){
  $uris = select_to_list($r, array("uri"));
  
  foreach($uris as $uri){
    if(is_string($uri)){
      $fn = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $uri);
      if(!file_exists("data/$fn.ttl")){
        $q = query_construct($uri);
        $t = execute_query($ep, $q);
        if($t){   
          $graph = new EasyRdf_Graph();
          $graph->parse($t, 'php');
          $f = write($graph->serialise('text/turtle'), $fn);
          echo "<p>$f</p>";
        }
      }
    }
  }
}

?>