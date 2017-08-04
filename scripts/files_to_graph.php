// <?
// phpinfo();
// die();
// set_time_limit(3600);
require_once('../vendor/init.php');

$posts = array();
$graph = new EasyRdf_Graph();
// $dir = "newdumpssince29july";
$dir = "../data/graphs_20170804";
// $dir = "../data/graphstest";

function graphname_from_filename($filename){
    $graphname = str_replace("%", "/", $filename);
    $parts = explode("__", $graphname);
    $graphname = $parts[0];
    return $graphname;
}

function insert_from($file, $dir, $ep){
    $ttl = file_get_contents($dir."/".$file);
    $graphname = graphname_from_filename($file);
    $q = query_insert_n($ttl, $graphname);
    echo "<pre style='height: 200px; width: 80%; overflow: scroll'>".htmlentities($q)."</pre>";
    $r = execute_query($ep, $q);
    if($r){
      echo "<p><strong>Success: ".$graphname."</strong></p>";
    }else{
      echo "<p><strong>Failed: ".$file."</strong></p>";
    }
}

function count_graph($graph, $ep){
    $q = query_select_count_graph($graph);
    $r = execute_query($ep, $q);
    return $r["rows"][0]["c"];
}

$files = scandir($dir);
foreach($files as $file){
  if($file != "." && $file != ".."){
/* 1. Check files parse as turtle */
    try{
        $ttl = file_get_contents("$dir/".$file);
        // $graph->parse($ttl, 'ttl'); // Parsing is for losers anyway
        if(isset($_GET['file']) && $_GET['file'] == $file){
            insert_from($file, $dir, $ep);
        }
        echo $file."<br/>";
        echo "<pre style='height: 200px; width: 80%; margin-left: auto; margin-right: auto; overflow: scroll'>".htmlentities($ttl)."</pre>";
        echo "<p><a href='?file=".$file."'>Store</a> (".filesize($dir."/".$file).")</p>";
        echo "<p>".count_graph(graphname_from_filename($file), $ep)." triples in this graph.";
        echo "<p>".count(file($dir."/".$file))." triples in this file.";
      
    }catch(Exception $e){
      echo "Failed: ".$file."<br/>";
      var_dump($e->getMessage());
      echo "<br/>";
    }
    echo "<hr/>";
  }
}

?>