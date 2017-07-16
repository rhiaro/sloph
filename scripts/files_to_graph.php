// <?
// phpinfo();
// die();
// set_time_limit(3600);
require_once('../vendor/init.php');

$posts = array();
$graph = new EasyRdf_Graph();
// $dir = "newdumpssince29july";
$dir = "../data/graphs_20170716";
// $dir = "../data/graphstest";

function insert_from($file, $dir, $ep){
    $ttl = file_get_contents($dir."/".$file);
    $graphname = str_replace("%", "/", $file);
    $parts = explode("__", $graphname);
    $graphname = $parts[0];
    $q = query_insert_n($ttl, $graphname);
    echo "<pre style='height: 200px; overflow: scroll'>".htmlentities($q)."</pre>";
    $r = execute_query($ep, $q);
    if($r){
      echo "<p><strong>Success: ".$graphname."</strong></p>";
    }else{
      echo "<p><strong>Failed: ".$file."</strong></p>";
    }
}

$files = scandir($dir);
foreach($files as $file){
  if($file != "." && $file != ".."){
/* 1. Check files parse as turtle */
    try{
        $ttl = file_get_contents("$dir/".$file);
        // $graph->parse($ttl, 'ttl'); // Parsing is for losers anyway
        echo $file."<br/>";
        if(filesize($dir."/".$file) > 800000 && !isset($_GET['file'])){
            echo "<pre style='height: 200px; overflow: scroll'>".htmlentities($ttl)."</pre>";
            echo "<p><a href='?file=".$file."'>Store</a> (".filesize($dir."/".$file).")</p>";
        }else{
            if(isset($_GET['file']) && $_GET['file'] == $file){
                echo "BLAH";
                insert_from($file, $dir, $ep);
            }else{
                insert_from($file, $dir, $ep); // Small enough to insert anyway
            }
        }
      
    }catch(Exception $e){
      echo "Failed: ".$file."<br/>";
      var_dump($e->getMessage());
      echo "<br/>";
    }
    echo "<hr/>";
  }
}

?>