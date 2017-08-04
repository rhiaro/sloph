<?
require_once('../vendor/init.php');
header('Content-Type: text/plain');

if(isset($_GET['offset'])){
  $offset = $_GET['offset'];
}else{
  $offset = 0;
}

function write($data, $fn){
    
  $log = "transformdumpsfeb17/$fn.ttl";

  $h = fopen($log, 'w');
  fwrite($h, $data);
  var_dump($h);
  fclose($h);
  return $log;
}

function select_graph(){
  $q = "@prefix as: <http://www.w3.org/ns/activitystreams#> .
select ?g ?s ?p ?o where {
  graph ?g { ?s ?p ?o . }
  filter (regex(str(?p),\"^http://www.w3.org/ns/activitystreams#\"))
}
ORDER BY ?s
";
  return $q;
}

function count_as($ep){
  $q = "@prefix as: <http://www.w3.org/ns/activitystreams#> .

select count(?p) as ?c where {
 graph ?g { ?s ?p ?o . }
 filter (regex(str(?p),\"^http://www.w3.org/ns/activitystreams#\"))
}";
  $r = execute_query($ep, $q);
  return $r['rows'][0]['c'];
}
function count_ass($ep){
  $q = "@prefix as: <https://www.w3.org/ns/activitystreams#> .

select count(?p) as ?c where {
 graph ?g { ?s ?p ?o . }
 filter (regex(str(?p),\"^https://www.w3.org/ns/activitystreams#\"))
}";
  $r = execute_query($ep, $q);
  return $r['rows'][0]['c'];
}

function literal_string($o_data, $datatype=true){
  if($o_data['o type'] == "literal"){
    $o = '"""'.$o_data['o'].'"""';
    if($datatype && isset($o_data['o datatype'])){
      $o .= "^^xsd:dateTime"; // i don't have anything other than datetimes..
    }
  }elseif($o_data['o type'] == "uri"){
    $o = '<'.$o_data['o'].'>';
  }
  return $o;
}

function query_p($g, $s, $p, $o, $oldo){
  $iq = "@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix ass: <https://www.w3.org/ns/activitystreams#> .

insert into <$g> {
    <$s> ass:$p $o .
} where {
    <$s> as:$p $o . 
}";

  $dq = "@prefix as: <http://www.w3.org/ns/activitystreams#> .
@prefix ass: <https://www.w3.org/ns/activitystreams#> .

delete {
    <$s> as:$p $oldo .
}";
  $qs = array('ins'=>$iq, 'del'=>$dq);
  return $qs;
}

$asps = array("name", "published", "updated", "summary", "content", "startTime", "endTime", "image", "inReplyTo", "location", "tag", "url", "to", "bto", "cc", "bcc", "duration", "actor", "object", "target", "origin", "result", "items", "relationship");
$asts = array("Actor", "Person", "Note", "Article", "Profile", "Organization", "Event", "Arrive", "Activity", "Object", "Like", "Announce", "Add", "Travel", "Accept", "Place", "Relationship", "Collection");

$select = select_graph();
$i = 0;
$triples = execute_query($ep, $select);
echo count_as($ep)." -> ".count_ass($ep)."\n----------------------\n";
// var_dump($triples);
foreach($triples['rows'] as $r){
  $i++;
  $g = $r['g'];
  $s = $r['s'];
  $p = str_replace("http://www.w3.org/ns/activitystreams#", "", $r['p']);
  $o = literal_string($r);
  $oldo = literal_string($r, false);
  $q = query_p($g, $s, $p, $o, $oldo);
  // var_dump($q);
  $ri = execute_query($ep, $q['ins']);
  if($ri){
  //   $rd = execute_query($ep, $q['del']);
  //   var_dump($rd);
  //   var_dump($q['del']);
  //   if($rd){
      echo "\nSuccess\n";
      echo "$g { <$s> <$p> $o }";
    // }else{
    //   echo "\nFailed to delete\n";
    //   echo "\n".$q['del']."\n";
    // }
  }else{
    echo "\nFailed to insert\n";
    echo "\n".$q['ins']."\n";
  }
  unset($ri); unset($rd);
  echo "\n".$i."\n";
}

// // Select all ?s
// $q1 = "SELECT DISTINCT ?s ?g WHERE { GRAPH ?g { ?s ?p ?o } }";
// $q2 = "SELECT DISTINCT ?s ?g WHERE { GRAPH ?g { ?s ?p ?o } filter (?g != \"http://blog.rhiaro.co.uk#\") }";
// $res1 = execute_query($ep, $q1);
// $res2 = execute_query($ep, $q2);
// // Construct all ?g { ?s ?p ?o . }
// foreach($res1["rows"] as $r){
//   echo "<p><input type=\"checkbox\" name=\"".$r["s"]."\" /> <input type\"text\" name=\"graph\" value=\"".$r["g"]."\" /> ".$r["s"]."</p>";
//   $q2 = "CONSTRUCT { <".$r["s"]."> ?p ?o . }
//   WHERE { GRAPH ?g { <".$r["s"]."> ?p ?o . } }";
//   // $res2 = execute_query($ep, $q2);
//   // Serialise turtle
//   // $graph = new EasyRdf_Graph();
//   // $graph->parse($res2, 'php');
//   // $ttl = $graph->serialise('text/turtle');
//   // // Find and replace http as to https
//   // $newttl = str_replace("http://www.w3.org/ns/activitystreams", "https://www.w3.org/ns/activitystreams", $ttl);
//   // echo "<pre>";
//   // echo htmlentities($newttl);
//   // echo "</pre>";
//   // Delete ?s ?p ?o
//   // While I'm here, check if the graph needs updating

//   // Insert new turtle
//   echo "<hr/>";
// }

?>