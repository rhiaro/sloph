<?

require_once('views.php');

/* Running queries */

function execute_query($ep, $query){
  $res = $ep->query($query);
  if(!$ep->getErrors()){
    return $res['result'];
  }else{
    var_dump($ep->getErrors());
  }
  return false;
}

/* Combining and munging queries */

function select_to_list($result, $types=array(), $key=null){
  if(!is_array($types) || !isset($types) || empty($types)) { $types = false; }
  
  if($key === null) { $var = $result['variables'][0]; }
  else { $var = $key; }
  
  $list = array();

  if(in_array($var, $result['variables'])){
    
    foreach($result['rows'] as $row){
      if(!$types || ($types && in_array($row[$var." type"], $types))){
        $list[] = $row[$var];
      }
    }

  }else{
    return false;
  }
  return $list;
}

function construct_uris($ep, $uris){
  $items = array();
  foreach($uris as $uri){
    if(is_string($uri)){
      $q = query_construct($uri);
      $r = execute_query($ep, $q);
      if($r){
        $items = array_merge($items, $r);
      }
    }
  }
  return $items;
}

/* Building queries */

function get_prefixes(){
  global $_PREF;
  $q = "";
  foreach($_PREF as $prefix => $ns){
    $q .= "PREFIX $prefix: <$ns>
";
  }
  return $q;
}

function query_construct($uri){
  $q = "CONSTRUCT { <$uri> ?p ?o . }
WHERE { <$uri> ?p ?o . }
";
  return $q;
}

function query_construct_all($limit){
  $q = "CONSTRUCT { ?s ?p ?o . }
WHERE { ?s ?p ?o . }
LIMIT $limit
";
  return $q;
}

function query_construct_graph($graph){
  $q = "CONSTRUCT { ?s ?p ?o . } 
WHERE { GRAPH <$graph> { ?s ?p ?o . } }";
  return $q;
}

function query_construct_type($type, $sort=null){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . }
WHERE { ?s ?p ?o. ?s a $type . ";
  if(isset($sort)){
    $q .= "?s $sort ?sort . ";
  }
  $q .="}";
  if(isset($sort)){
    $q .= "ORDER BY DESC(?sort)";
  }
  return $q;
}

function query_select_s($limit=0, $graph="http://blog.rhiaro.co.uk#"){
  
  if($graph === null){
    $graph = "?g";
  }else{
    $graph = "<$graph>";
  }

  $q = "SELECT DISTINCT ?s ";
  if($graph == "?g"){
    $q .= "?g ";
  }
  $q .= "WHERE {
  GRAPH $graph { ?s ?p ?o . }
}";
  if($limit > 0){
    $q .= "LIMIT $limit";
  }
  return $q;
}

function query_select_s_desc($limit=0, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
  ?s as:published ?d .
}
ORDER BY DESC(?d)";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_s_and_type_desc($limit=0, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s ?t ?l WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
  OPTIONAL { ?s a ?t }
  OPTIONAL { ?s as:location ?l }
  ?s as:published ?d .
}
ORDER BY DESC(?d)";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_all($limit, $graph="http://blog.rhiaro.co.uk#"){
  $q = "SELECT * WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
}
LIMIT $limit
";
  return $q;
}

function query_select_s_where($vals, $limit=10, $sort=null){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { \n";
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  $q .= "} ";
  if(isset($sort)){
    $q .= "\nORDER BY ASC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_o_where($vals, $limit=0, $sort=null){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { \n";
  foreach($vals as $s => $p){
    $q .= "<$s> $p ?s .\n";
  }
  $q .= "} ";
  if(isset($sort)){
    $q .= "\nORDER BY ASC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_s_views($score, $limit=10){
  $q = get_prefixes();
  $ps = score_predicates();
  
  $q .= "SELECT DISTINCT ?s WHERE { \n";
  foreach($ps as $i => $p){
    $q .= "  OPTIONAL { ?s ?$p ?v$i } \n";
    $q .= "  FILTER( ?v$i >= ".$score[$p]." ) \n";
  }
  $q .= "}\n";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_s_next($uri, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_next_of_type($uri, $type, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  ?s a $type . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_prev($uri, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_prev_of_type($uri, $type, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  ?s a $type . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_next_count($uri, $count=10, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT $count
";
  return $q;
}

function query_select_s_prev_count($uri, $count=10, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT $count
";
  return $q;
}

// HERENOW
function query_select_s_next_of_type_count($uri, $count=10, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT $count
";
  return $q;
}

function query_select_s_prev_of_type_count($uri, $count=10, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT $count
";
  return $q;
}

/* Specific queries */

function query_for_places(){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE {
  ?s a as:Place . ?s ?p ?o .
}";
  return $q;
}

function query_select_container_and_contents($container){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?c ?s ?p ?o WHERE {
  ?c a ldp:Container .
  ?c ldp:contains ?s .
  ?s ?p ?o .
";
  return $q;
}

function query_select_tags(){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?tag ?name COUNT(?p) AS ?c WHERE {
  ?p as:tag ?tag .
  OPTIONAL { ?tag as:name ?name . } 
}
GROUP BY ?tag
ORDER BY DESC(?c)";
  return $q;
}

/* The count is unpredictable.. messed up when ?o is an array...
function query_select_vars($vars, $vals, $limit=10, $sort=null){
  $q = get_prefixes();
  $q .= "SELECT ?s ?".implode($vars, ' ?')." WHERE { \n";
  foreach($vars as $predicate => $var){
    $q .= "?s $predicate ?$var .\n";
  }
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  $q .= "} ";
  if(isset($sort)){
    $q .= "\nORDER BY DESC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_construct_vars($vars, $vals, $limit=10, $sort=null){
  $limit = $limit * count($vars);
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE { \n
    ?s ?p ?o . \n";
  foreach($vars as $predicate => $var){
    $q .= "?s $predicate ?$var .\n";
  }
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  $q .= "} ";
  if(isset($sort)){
    $q .= "\nORDER BY DESC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}
*/

function query_select_hasPrimaryTopic($uri){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  ?s foaf:isPrimaryTopicOf <$uri> .
}
";
  return $q;
}

/* Setting */

function query_load($file, $graph="http://blog.rhiaro.co.uk#"){
  $q = "LOAD <$file> INTO <$graph>";
  return $q;
}

function query_delete($uri){
  $q = "DELETE { <$uri> ?p ?o . }";
  return $q;
}

function query_insert($turtle, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { ".$turtle." }";
  return $q;
}

function query_add_to_graph($uri, $graph, $from="?g"){

  if($from != "?g"){
    $from = "<$from>";
  }

  $q = "INSERT INTO <$graph> { <$uri> ?p ?o . } 
WHERE { GRAPH $from { <$uri> ?p ?o . } }";
  return $q;
}

function query_remove_from_graph($uri, $graph){
  $q = "DELETE FROM <$graph> { <$uri> ?p ?o . } ";
  return $q;
}

function query_insert_items($collection, $items, $graph="http://blog.rhiaro.co.uk#"){
  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { ";
  $q .= "  <$collection> as:items ";
  foreach($items as $item){
    $q .= "<$item>, ";
  }
  $q = rtrim($q, ", ");
  $q .= " . }";

  return $q;
}

function query_insert_add($uri, $collection, $items, $published, $summary, $content="", $graph="http://blog.rhiaro.co.uk#"){

  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { ";
  $q .= "  <$uri> a as:Add .";
  $q .= "  <$uri> as:summary \"\"\"$summary\"\"\" .";
  if(strlen($content) > 0){
    $q .= "  <$uri> as:content \"\"\"$content\"\"\" .";
  }
  $q .= "  <$uri> as:published \"$published\"^^xsd:dateTime .";
  $q .= "  <$uri> as:target <$collection> .";
  $q .= "  <$uri> as:object ";
  foreach($items as $item){
    $q .= "<$item>, ";
  }
  $q = rtrim($q, ", ");
  $q .= " . }";

  return $q;
}

?>