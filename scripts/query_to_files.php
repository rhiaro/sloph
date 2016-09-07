<?
require_once('../vendor/init.php');

function write($data, $fn){
    
  $log = "newdumpssince29july/$fn.ttl";

  $h = fopen($log, 'w');
  fwrite($h, $data);
  var_dump($h);
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
      if(!file_exists("newdumpssince29july/$fn.ttl")){
        $q = query_construct($uri);
        $t = execute_query($ep, $q);
        if($t){ 
          foreach($t as $uri => $stuff){
            $https = str_replace("http://rhiaro.co.uk", "https://rhiaro.co.uk", $uri);
            unset($t[$uri]);
            $t[$https] = $stuff;
          }
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