<?
set_time_limit(360);
require_once('../vendor/init.php');

$posts = array();
$graph = new EasyRdf_Graph();
$dir = "datahttps";

$files = scandir($dir);
foreach($files as $file){
  if($file != "." && $file != ".."){
    $ttl = file_get_contents("$dir/".$file);
    $graph->parse($ttl, 'turtle');
    var_dump($graph->serialise('php'));
    // $q = query_load("$dir/".$file);
    // echo htmlentities($q);
    // $r = execute_query($ep, $q);
    // if($r){
    //   echo "Success: ".$file."<br/>";
    // }else{
    //   echo "Failed: ".$file;
    // }
    echo "<hr/>";
    // try{
    //   $ttl = file_get_contents("$dir/".$file);
    //   $graph->parse($ttl, 'ttl');
    //   
    // }catch(Exception $e){
    //   echo "Failed: ".$file."<br/>";
    //   var_dump($e->getMessage());
    //   echo "<br/>";
    // }
  }
}

?>