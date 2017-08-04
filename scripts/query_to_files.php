<?
require_once('../vendor/init.php');

function write($data, $fn){
    
  $log = "../data/graphs_20170804/$fn";

  $h = fopen($log, 'a');
  fwrite($h, $data);
  // var_dump($h);
  fclose($h);
  return $log;
}

function select_triple($ep){
  $all = array();
  $q = "SELECT ?g ?s ?p ?o
WHERE { GRAPH ?g { ?s ?p ?o . } }
ORDER BY ?g
OFFSET 100000
LIMIT 50000
  ";
  $r = execute_query($ep, $q);
  $parser = ARC2::getRDFParser();
  foreach($r["rows"] as $res){
      $ttl = $parser->toNTriples(array($res));
    if(!array_key_exists($res["g"], $all)){
      $all = array_merge($all, array($res["g"] => array($ttl)));
    }else{
      $all[$res["g"]][] = $ttl;
    }
  }

  foreach($all as $graph => $triples){
    $data = implode("\n", $triples);
    echo write($data, str_replace("/", "%", $graph))."\n";
  }

}

function select_dated($ep){
  $all = array();
  $q = "PREFIX as: <http://www.w3.org/ns/activitystreams#>
SELECT ?g ?s ?p ?o
WHERE { 
  {
    GRAPH ?g { ?s ?p ?o . } 
    GRAPH ?g { ?s as:published ?d . } 
    FILTER ( ?d > \"2017-07-15T00:00:00+01:00\" )
  } UNION {
    GRAPH ?g { ?s ?p ?o . } 
    GRAPH ?g { ?s as:updated ?u . } 
    FILTER ( ?u > \"2017-07-15T00:00:00+01:00\" )
  }
}
ORDER BY ?g
LIMIT 50000
  ";
  $r = execute_query($ep, $q);
  $parser = ARC2::getRDFParser();
  foreach($r["rows"] as $res){
      $ttl = $parser->toNTriples(array($res));
    if(!array_key_exists($res["g"], $all)){
      $all = array_merge($all, array($res["g"] => array($ttl)));
    }else{
      $all[$res["g"]][] = $ttl;
    }
  }

  foreach($all as $graph => $triples){
    $data = implode("\n", $triples);
    echo write($data, str_replace("/", "%", $graph))."\n";
  }
}

// select_triple($ep);
select_dated($ep);

// $posts = array();

// $q = query_select_s();
// $r = execute_query($ep, $q);
// if($r){
//   $uris = select_to_list($r, array("uri"));
  
//   foreach($uris as $uri){
//     if(is_string($uri)){
//       $fn = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '_', $uri);
//       if(!file_exists("newdumpssince29july/$fn.ttl")){
//         $q = query_construct($uri);
//         $t = execute_query($ep, $q);
//         if($t){ 
//           foreach($t as $uri => $stuff){
//             $https = str_replace("http://rhiaro.co.uk", "https://rhiaro.co.uk", $uri);
//             unset($t[$uri]);
//             $t[$https] = $stuff;
//           }
//           $graph = new EasyRdf_Graph();
//           $graph->parse($t, 'php');
//           $f = write($graph->serialise('text/turtle'), $fn);
//           echo "<p>$f</p>";
//         }
//       }
//     }
//   }
// }

?>