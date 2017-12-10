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

function construct_uris_in_graph($ep, $uris, $graph){
  $items = array();
  foreach($uris as $uri){
    if(is_string($uri)){
      $q = query_construct_uri_graph($uri, $graph);
      $r = execute_query($ep, $q);
      if($r){
        $items = array_merge($items, $r);
      }
    }
  }
  return $items;
}

function construct_and_sort($ep, $uris, $sort="as:published"){
  $items = construct_uris($ep, $uris);
  $order = array();
  foreach($items as $uri => $data){
    $order[$uri] = get_value(array($uri => $data), $sort);
  }
  arsort($order);
  $sorted = array();
  foreach($order as $uri => $val){
    $sorted[$uri] = $items[$uri];
  }
  return $sorted;
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

function query_construct_outbox($graph="https://blog.rhiaro.co.uk/"){
  // This is temporary and should be replaced with proper paging and stuff.
  // The particular properties are to help dokieli for now.
  // Ultimately it should return everything, but paged.
  $q = get_prefixes();
  $q .= "CONSTRUCT {
  <https://rhiaro.co.uk/outgoing/> a as:OrderedCollection .
  <https://rhiaro.co.uk/outgoing/> as:items ?s .
  ?s a ?t .
  ?s as:published ?d .
  ?s as:target ?target .
  ?s as:object ?object .
  ?s as:inReplyTo ?repl .
}WHERE {
  GRAPH <$graph> { 
    ?s a ?t . 
    ?s as:published ?d .
    OPTIONal { ?s as:target ?target . }
    OPTIONal { ?s as:object ?object . }
    OPTIONal { ?s as:inReplyTo ?repl . }
  }
}
ORDER BY DESC(?d)
LIMIT 15000"; // Graph times out if I make it get everything
  return $q;
}


function query_construct_uri_graph($uri, $graph){
  $q = "CONSTRUCT { <$uri> ?p ?o . } 
WHERE { GRAPH <$graph> { <$uri> ?p ?o . } }";
  return $q;
}

function query_construct_graphs($graphs){
  if(count($graphs) < 2){
    return query_construct_graph($graphs[0]);
  }
  $q = "CONSTRUCT { ?s ?p ?o . } WHERE {";
  foreach($graphs as $graph){
    $i = 0;
    $q .= "  { GRAPH <$graph> { ?s ?p ?o . } }";
    if($i < count($graphs)){
      $q .= "  UNION ";
    }
    $i++;
  }
  $q .= "}";
  return $q;
}

function query_construct_uri_graphs($uri, $graphs){
  if(count($graphs) < 2){
    return query_construct_graph($graphs[0]);
  }
  $q = "CONSTRUCT { <$uri> ?p ?o . } WHERE {";
  foreach($graphs as $graph){
    $i = 0;
    $q .= "  { GRAPH <$graph> { <$uri> ?p ?o . } }";
    if($i < count($graphs)){
      $q .= "  UNION ";
    }
    $i++;
  }
  $q .= "}";
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

function construct_between($from, $to){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE { ?s ?p ?o . \n";
  $q .= " ?s as:published ?d . \n";
  $q .= " FILTER(?d > \"$from\") . \n";
  $q .= " FILTER(?d <= \"$to\") . \n";
  $q .= "} \n";
  $q .= "ORDER BY DESC(?d)";
  return $q;
}

function query_construct_collection_page($page_uri, $collection){
  $q = get_prefixes();
  $q .= "CONSTRUCT { 
  <$page_uri> a as:CollectionPage .
  <$page_uri> as:name ?name .
  <$page_uri> as:partOf <$collection> .
} WHERE {
  <$collection> as:name ?name .
}";
  return $q;
}

function query_get_graphs(){
  $q = "SELECT DISTINCT ?g WHERE {
  GRAPH ?g { ?s ?p ?o . }
}";
  return $q;
}

function query_select_s($limit=0, $graph="https://blog.rhiaro.co.uk/"){
  
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

function query_select_s_type($type, $sort="as:published", $dir="DESC", $limit=0, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  GRAPH <$graph> { ?s a $type .";
  if(isset($sort)){
    $q .= "  ?s $sort ?sort .";
  }
  $q .= "  }
}";
  if(isset($sort)){
    $q .= "ORDER BY $dir(?sort)";
  }
  if($limit > 0){
    $q .= "LIMIT $limit";
  }
  return $q;
}

function query_select_count_graph($graph="https://blog.rhiaro.co.uk/"){
  $q = "SELECT COUNT(?s) AS ?c WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
}";
  return $q;
}

function query_select_s_desc($limit=0, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_and_type_desc($limit=0, $graph="https://blog.rhiaro.co.uk/"){
  $now = new DateTime();
  $now = $now->format(DATE_ATOM);
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s ?t ?l WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
  OPTIONAL { ?s a ?t }
  OPTIONAL { ?s as:location ?l }
  ?s as:published ?d .
  FILTER ( ?d < \"$now\" ) . 
}
ORDER BY DESC(?d)";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_all($limit, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_next($uri, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_one_of_type($type, $sort="as:published", $dir="DESC", $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT ?s WHERE {
  GRAPH <$graph> {
    ?s a $type .";
  if(isset($sort)){
    $q .= "    ?s $sort ?sort .";
  }
  $q .=" }
}";
  if(isset($sort)){
    $q .= "ORDER BY $dir(?sort)";
  }
  $q .= "LIMIT 1";
  return $q;
}

function query_select_s_next_of_type($uri, $type, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_prev($uri, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_prev_of_type($uri, $type, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_next_count($uri, $count=10, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_prev_count($uri, $count=10, $graph="https://blog.rhiaro.co.uk/"){
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

function query_select_s_next_of_type_count($uri, $count=10, $type=null, $graph="https://blog.rhiaro.co.uk/"){
  
  if($type === null){
    return query_select_s_next_count($uri, $count, $graph);
  }else{
    $q = get_prefixes();

    $q .= "SELECT DISTINCT ?s WHERE { \n";
    $q .= " GRAPH <$graph> {";
    $q .= "  ?s as:published ?d . \n";
    $q .= "  ?s a $type . \n";
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
}

function query_select_s_prev_of_type_count($uri, $count=10, $type=null, $graph="https://blog.rhiaro.co.uk/"){
  if($type === null){
    return query_select_s_prev_count($uri, $count, $graph);
  }else{
    $q = get_prefixes();

    $q .= "SELECT DISTINCT ?s WHERE { \n";
    $q .= " GRAPH <$graph> {";
    $q .= "  ?s as:published ?d . \n";
    $q .= "  ?s a $type . \n";
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
}

function query_select_next_items($collection, $after, $sortby="as:published", $count=16){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  <$collection> as:items ?s .
  ?s $sortby ?sort .";

  if(isset($after)){
    $q .=  "
  <$after> $sortby ?sortafter .
  FILTER ( ?sort > ?sortafter ) .\n";
  }
  $q .= "}
  ORDER BY ASC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_next_type($type, $before, $sortby="as:published", $count=16, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { GRAPH <$graph> {
  ?s rdf:type $type .
  ?s $sortby ?sort . }";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortafter .
  FILTER ( ?sort > ?sortafter ) .\n";
  }
  $q .= "}
  ORDER BY ASC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_prev_type($type, $before, $sortby="as:published", $count=16, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { GRAPH <$graph> {
  ?s rdf:type $type .
  ?s $sortby ?sort . }";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortbefore .
  FILTER ( ?sort < ?sortbefore ) .\n";
  }
  $q .= "}
  ORDER BY DESC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_prev_items($collection, $before, $sortby="as:published", $count=16){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  <$collection> as:items ?s .
  ?s $sortby ?sort .";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortbefore .
  FILTER ( ?sort < ?sortbefore ) .\n";
  }
  $q .= "}
  ORDER BY DESC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_count_items($collection){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT COUNT(?item) AS ?c WHERE {
  <$collection> as:items ?item .
}
GROUP BY ?s";
  return $q;
}

function query_count_type($type, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT COUNT(?s) AS ?c WHERE {
  ?s rdf:type $type .
}";
  return $q;
}

/* Specific queries */

function query_for_places(){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE {
  GRAPH <https://rhiaro.co.uk/locations/> { ?s a as:Place . ?s ?p ?o . }
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

function query_load($file, $graph="https://blog.rhiaro.co.uk/"){
  $q = "LOAD <$file> INTO <$graph>";
  return $q;
}

function query_delete($uri){
  $q = "DELETE { <$uri> ?p ?o . }";
  return $q;
}

function query_insert($turtle, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { ".$turtle." }";
  return $q;
}

function query_insert_n($ntriples, $graph="https://blog.rhiaro.co.uk/"){
  $q = "INSERT INTO <$graph> { ".$ntriples." }";
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

function query_insert_items($collection, $items, $graph="https://blog.rhiaro.co.uk/"){
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

function query_insert_add($uri, $collection, $items, $published, $summary, $content="", $graph="https://blog.rhiaro.co.uk/"){

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