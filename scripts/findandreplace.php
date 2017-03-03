<?
require_once('../vendor/init.php');

// Select all ?s
$q1 = "SELECT DISTINCT ?s ?g WHERE { GRAPH ?g { ?s ?p ?o } }";
$q2 = "SELECT DISTINCT ?s ?g WHERE { GRAPH ?g { ?s ?p ?o } filter (?g != \"http://blog.rhiaro.co.uk#\") }";
$res1 = execute_query($ep, $q1);
$res2 = execute_query($ep, $q2);
// Construct all ?g { ?s ?p ?o . }
foreach($res1["rows"] as $r){
  echo "<p><input type=\"checkbox\" name=\"".$r["s"]."\" /> <input type\"text\" name=\"graph\" value=\"".$r["g"]."\" /> ".$r["s"]."</p>";
  $q2 = "CONSTRUCT { <".$r["s"]."> ?p ?o . }
  WHERE { GRAPH ?g { <".$r["s"]."> ?p ?o . } }";
  // $res2 = execute_query($ep, $q2);
  // Serialise turtle
  // $graph = new EasyRdf_Graph();
  // $graph->parse($res2, 'php');
  // $ttl = $graph->serialise('text/turtle');
  // // Find and replace http as to https
  // $newttl = str_replace("http://www.w3.org/ns/activitystreams", "https://www.w3.org/ns/activitystreams", $ttl);
  // echo "<pre>";
  // echo htmlentities($newttl);
  // echo "</pre>";
  // Delete ?s ?p ?o
  // While I'm here, check if the graph needs updating

  // Insert new turtle
  echo "<hr/>";
}

?>