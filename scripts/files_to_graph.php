<?
set_time_limit(360);
require_once('../vendor/init.php');

$posts = array();
$graph = new EasyRdf_Graph();
$dir = "newdumpssince29july";

$files = scandir($dir);
foreach($files as $file){
  if($file != "." && $file != ".."){
/* 1. Check files parse as turtle */
    // try{
    //   $ttl = file_get_contents("$dir/".$file);
    //   $graph->parse($ttl, 'ttl');
    //   // var_dump($graph->serialise('php'));
    //   echo $file."<br/>";
      
    // }catch(Exception $e){
    //   echo "Failed: ".$file."<br/>";
    //   var_dump($e->getMessage());
    //   echo "<br/>";
    // }
/* 2. Parse files and insert */
    // $ttl = file_get_contents("$dir/".$file);
    // $graph->parse($ttl, 'turtle');
    // $q = query_load("$dir/".$file);
    // echo htmlentities($q);
    // $r = execute_query($ep, $q);
    // if($r){
    //   echo "Success: ".$file."<br/>";
    // }else{
    //   echo "Failed: ".$file;
    // }
    // echo "<hr/>";
  }
}

?>