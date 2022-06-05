<?

require_once('views.php');

/* Helpers */

function sparql_date($datetime){
  $d = $datetime->format(DATE_ATOM);
  return '"'.$d.'"^^xsd:dateTime';
}

function datenow(){
  return sparql_date(new DateTime());
}

function not_in_future($s="?s"){
  $now = datenow();
  $q = "
OPTIONAL { $s as:published ?date . }
  FILTER(!BOUND(?date) || ?date < $now) .
";
  return $q;
}

function is_unique($ep, $uri, $future=True){
  $q = get_prefixes();
  $q .= "SELECT ?o WHERE {
  <$uri> ?p ?o .";
  if(!$future){
    $q .= not_in_future("<$uri>");
  }
  $q .= "} LIMIT 1";
  $res = $ep->query($q);
  if(empty($res['result']['rows'])){
    return true;
  }else{
    return false;
  }
}

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

function select_to_list_sorted($result, $sortkey, $types=array(), $key=null){
  if(!is_array($types) || !isset($types) || empty($types)) { $types = false; }

  if($key === null) { $var = $result['variables'][0]; }
  else { $var = $key; }

  $list = array();

  if(in_array($var, $result['variables'])){

    foreach($result['rows'] as $row){
      if(!$types || ($types && in_array($row[$var." type"], $types))){
        $list[$row[$sortkey]] = $row[$var];
      }
    }

  }else{
    return false;
  }
  ksort($list);
  return array_values($list);
}

function construct_uris($ep, $uris, $future=False){
  $items = array();
  foreach($uris as $uri){
    if(is_string($uri)){
      $q = query_construct($uri, $future);
      $r = execute_query($ep, $q);
      if($r){
        $items = array_merge($items, $r);
      }
    }
  }
  return $items;
}

function construct_uris_in_graph($ep, $uris, $graph, $future=False){
  $items = array();
  foreach($uris as $uri){
    if(is_string($uri)){
      $q = query_construct_uri_graph($uri, $graph, $future);
      $r = execute_query($ep, $q);
      if($r){
        $items = array_merge($items, $r);
      }
    }
  }
  return $items;
}

function construct_and_sort($ep, $uris, $sort="as:published", $future=False){
  $items = construct_uris($ep, $uris, $future);
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

function construct_last_of_type($ep, $type, $sort="as:published", $future=False){
  $q = query_select_s_type($type, $sort, "DESC", 1, "https://blog.rhiaro.co.uk/", $future);
  $res = execute_query($ep, $q);
  if($res && !empty($res["rows"])){
    $uri = select_to_list($res);
    $obj = execute_query($ep, query_construct($uri[0]));
    return $obj;
  }
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

function query_construct($uri, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT { <$uri> ?p ?o . }
WHERE {
  <$uri> ?p ?o . \n";
  if(!$future){
    $q .= not_in_future("<$uri>");
  }
  $q .= "}";
  return $q;
}

function query_construct_all($limit){
  $q = "CONSTRUCT { ?s ?p ?o . }
WHERE { ?s ?p ?o . }
LIMIT $limit
";
  return $q;
}

function query_construct_graph($graph, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . }
WHERE { GRAPH <$graph> { ?s ?p ?o . ";
  if(!$future){
    $q .= not_in_future();
  }
$q .= "} }";
  return $q;
}

function query_select_timezone(){
  $q = get_prefixes();
  $q .= "SELECT ?tz WHERE { ?s a time:TimeZone . ?s time:timeZone ?tz . } ";
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


function query_construct_uri_graph($uri, $graph, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT { <$uri> ?p ?o . }
WHERE {
  GRAPH <$graph> {
    <$uri> ?p ?o .
";
  if(!$future){
    $q .= not_in_future("<$uri>");
  }
  $q .= "  }
}";
  return $q;
}

function query_construct_graphs($graphs, $future=False){
  $q = get_prefixes();
  if(count($graphs) < 2){
    return query_construct_graph($graphs[0], $future);
  }
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE {";
  foreach($graphs as $graph){
    $i = 0;
    $q .= "  { GRAPH <$graph> { ?s ?p ?o . ";
    if(!$future){
      $q .= not_in_future();
    }
    $q .= "} }";
    if($i < count($graphs)){
      $q .= "  UNION ";
    }
    $i++;
  }
  $q .= "}";
  return $q;
}

function query_construct_uri_graphs($uri, $graphs, $future=False){
  $q = get_prefixes();
  if(count($graphs) < 2){
    return query_construct_graph($graphs[0], $future);
  }
  $q .= "CONSTRUCT { <$uri> ?p ?o . } WHERE {";
  foreach($graphs as $graph){
    $i = 0;
    $q .= "  { GRAPH <$graph> { <$uri> ?p ?o . ";
    if(!$future){
      $q .= not_in_future("<$uri>");
    }
    $q .= "} }";
    if($i < count($graphs)){
      $q .= "  UNION ";
    }
    $i++;
  }
  $q .= "}";
  return $q;
}

function query_construct_type($type, $sort=null, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . }
WHERE { ?s ?p ?o. ?s a $type . ";
  if(isset($sort)){
    $q .= "?s $sort ?sort . ";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .="}";
  if(isset($sort)){
    $q .= "ORDER BY DESC(?sort)";
  }
  return $q;
}

function construct_between($from, $to, $future=False){
  if(!$future){
    $from = force_present($from, DATE_ATOM);
    $to = force_present($to, DATE_ATOM);
  }
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

function query_construct_collections_from_adds(){
  $q = get_prefixes();
  $q .= "CONSTRUCT {
  ?coll a as:Collection .
  ?coll as:items ?item .
  ?coll as:name ?name .
  ?coll as:summary ?summary .
  ?coll as:content ?content .
  ?coll as:tag ?tag .
  ?coll as:image ?image .
  ?coll as:updated ?upd .
  ?coll rdf:type ?type .
} WHERE {
  ?add a as:Add .
  ?add as:target ?coll .
  ?add as:object ?item .
  OPTIONAL { ?add as:published ?upd . }
  OPTIONAL { ?coll as:name ?name . }
  OPTIONAL { ?coll as:summary ?summary . }
  OPTIONAL { ?coll as:content ?content . }
  OPTIONAL { ?coll as:tag ?tag . }
  OPTIONAL { ?coll rdf:type ?type . }
  OPTIONAL { ?coll as:image ?image . }
}
    ";
  return $q;
}

function query_get_graphs(){
  $q = "SELECT DISTINCT ?g WHERE {
  GRAPH ?g { ?s ?p ?o . }
}";
  return $q;
}

function query_select_s($limit=0, $graph="https://blog.rhiaro.co.uk/", $future=False){

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
  GRAPH $graph { ?s ?p ?o . \n";
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "}
}";
  if($limit > 0){
    $q .= "LIMIT $limit";
  }
  return $q;
}

function query_select_s_type($type, $sort="as:published", $dir="DESC", $limit=0, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  GRAPH <$graph> { ?s a $type .";
  if(isset($sort)){
    $q .= "  ?s $sort ?sort .";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "  }
}
";
  if(isset($sort)){
    $q .= "ORDER BY $dir(?sort)
";
  }
  if($limit > 0){
    $q .= "LIMIT $limit";
  }
  return $q;
}

function query_select_s_type_sort($type, $sort="as:published", $dir="DESC", $limit=0, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s ?sort WHERE {
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

function query_select_s_between($from, $to, $graph="https://blog.rhiaro.co.uk/", $future=False){
  if(!$future){
    $from = force_present($from, DATE_ATOM);
    $to = force_present($to, DATE_ATOM);
  }
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  GRAPH <$graph> { ?s as:published ?d . }
  FILTER(?d > \"$from\")
  FILTER(?d <= \"$to\")
}
ORDER BY ASC(?d)";
  return $q;
}

function query_select_s_between_types($from, $to, $types, $graph="https://blog.rhiaro.co.uk/", $future=False){
  if(!$future){
    $from = force_present($from, DATE_ATOM);
    $to = force_present($to, DATE_ATOM);
  }
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s ?d WHERE {";
  foreach($types as $type){
    $i = 0;
    $q .= "  { GRAPH <$graph> { ?s a $type . ?s as:published ?d . } }";
    if($i < count($types)){
      $q .= "  UNION ";
    }
    $i++;
  }
  $q .= "
  FILTER(?d > \"$from\")
  FILTER(?d <= \"$to\")
}
ORDER BY ASC(?d)";
  return $q;
}

function query_select_count_between_type($from, $to, $type, $graph="https://blog.rhiaro.co.uk/", $future=False){
  if(!$future){
    $from = force_present($from, DATE_ATOM);
    $to = force_present($to, DATE_ATOM);
  }
  $q = get_prefixes();
  $q .= "SELECT COUNT(?s) AS ?c WHERE {
  GRAPH <$graph> {
    ?s a $type .
    ?s as:published ?d .
  }
  FILTER(?d > \"$from\")
  FILTER(?d <= \"$to\")
}
ORDER BY ASC(?d)";
  return $q;
}

function query_select_count_graph($graph="https://blog.rhiaro.co.uk/"){
  $q = "SELECT COUNT(?s) AS ?c WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
}";
  return $q;
}

function query_select_s_desc($limit=0, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  GRAPH <$graph> { ?s ?p ?o . }
  ?s as:published ?d .";
  if(!$future){
    $q .= "FILTER( ?d < ".datenow()." ) .";
  }
  $q .= "
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

function query_select_s_where($vals, $limit=10, $sort=null, $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { \n";
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  if(!$future){
    $q .= not_in_future();
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

function query_select_o($s, $p, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT ?o WHERE { GRAPH <$graph> { <$s> $p ?o . } }";
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

function query_select_s_next($uri, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT ?s ?d WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
";
  return $q;
}

function query_select_one_of_type($type, $sort="as:published", $dir="DESC", $graph="https://blog.rhiaro.co.uk/"){
  // Deprecated?
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

function query_select_s_next_of_type($uri, $type, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT ?s ?d WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  ?s a $type . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_prev($uri, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_prev_of_type($uri, $type, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  ?s a $type . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_next_count($uri, $count=10, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT $count
";
  return $q;
}

function query_select_s_prev_count($uri, $count=10, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();

  $q .= "SELECT DISTINCT ?s WHERE { \n";
  $q .= " GRAPH <$graph> {";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= " }";
  $q .= "  FILTER ( ?d < ?d2 ) . \n";
  if(!$future){
    $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
  }
  $q .= "  FILTER ( <$uri> != ?s ) . \n";
  $q .= "}
ORDER BY DESC(?d)
LIMIT $count
";
  return $q;
}

function query_select_s_next_of_type_count($uri, $count=10, $type=null, $graph="https://blog.rhiaro.co.uk/", $future=False){

  if($type === null){
    return query_select_s_next_count($uri, $count, $graph, $future);
  }else{
    $q = get_prefixes();

    $q .= "SELECT DISTINCT ?s WHERE { \n";
    $q .= " GRAPH <$graph> {";
    $q .= "  ?s as:published ?d . \n";
    $q .= "  ?s a $type . \n";
    $q .= "  <$uri> as:published ?d2 . \n";
    $q .= " }";
    $q .= "  FILTER ( ?d > ?d2 ) . \n";
    if(!$future){
      $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
    }
    $q .= "  FILTER ( <$uri> != ?s ) . \n";
    $q .= "}
  ORDER BY ASC(?d)
  LIMIT $count
  ";
    return $q;
  }
}

function query_select_s_prev_of_type_count($uri, $count=10, $type=null, $graph="https://blog.rhiaro.co.uk/", $future=False){
  if($type === null){
    return query_select_s_prev_count($uri, $count, $graph, $future);
  }else{
    $q = get_prefixes();

    $q .= "SELECT DISTINCT ?s WHERE { \n";
    $q .= " GRAPH <$graph> {";
    $q .= "  ?s as:published ?d . \n";
    $q .= "  ?s a $type . \n";
    $q .= "  <$uri> as:published ?d2 . \n";
    $q .= " }";
    $q .= "  FILTER ( ?d < ?d2 ) . \n";
    if(!$future){
      $q .= "  FILTER( ?d <= ".datenow()." ) . \n";
    }
    $q .= "  FILTER ( <$uri> != ?s ) . \n";
    $q .= "}
  ORDER BY DESC(?d)
  LIMIT $count
  ";
    return $q;
  }
}

function query_select_next_items($collection, $after, $sortby="as:published", $count=16, $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  <$collection> as:items ?s .
  ?s $sortby ?sort .";

  if(isset($after)){
    $q .=  "
  <$after> $sortby ?sortafter .
  FILTER ( ?sort > ?sortafter ) .\n";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "}
  ORDER BY ASC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_next_type($type, $before, $sortby="as:published", $count=16, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { GRAPH <$graph> {
  ?s rdf:type $type .
  ?s $sortby ?sort . }";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortafter .
  FILTER ( ?sort > ?sortafter ) .\n";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "}
  ORDER BY ASC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_prev_type($type, $before, $sortby="as:published", $count=16, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE { GRAPH <$graph> {
  ?s rdf:type $type .
  ?s $sortby ?sort . }";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortbefore .
  FILTER ( ?sort < ?sortbefore ) .\n";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "}
  ORDER BY DESC(?sort)";
  if($count > 0){
    $q .= "
    LIMIT $count";
  }
  return $q;
}

function query_select_prev_items($collection, $before, $sortby="as:published", $count=16, $future=False){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  <$collection> as:items ?s .
  ?s $sortby ?sort .";

  if(isset($before)){
    $q .=  "
  <$before> $sortby ?sortbefore .
  FILTER ( ?sort < ?sortbefore ) .\n";
  }
  if(!$future){
    $q .= not_in_future();
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
}";
  return $q;
}

function query_count_added_items($collection){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT COUNT(?item) AS ?c WHERE {
  ?add as:object ?item .
  ?add as:target <$collection> .
}";
  return $q;
}

function query_count_type($type, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT COUNT(?s) AS ?c WHERE {
  GRAPH <$graph> { ?s rdf:type $type . }
}";
  return $q;
}

function query_select_image($collection=null, $limit=1, $graph="https://blog.rhiaro.co.uk/"){

  if(empty($collection)){
    $collection = "?coll";
  }else{
    $collection = "<$collection>";
  }

  $q = get_prefixes();
  $q .= "SELECT ?img WHERE {
  ?add a as:Add .
  ?add as:target $collection .
  ?add as:object ?img .
}
LIMIT $limit";

  return $q;
}

/* Specific queries */

function query_for_theme($uri="https://rhiaro.co.uk/", $graph="https://rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "SELECT ?color ?image WHERE {
  GRAPH <$graph> { 
    <$uri> as:image ?image .
    <$uri> view:color ?color .
  }
}";
  return $q;
}

function query_for_places(){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE {
  GRAPH <https://rhiaro.co.uk/locations/> { ?s a as:Place . ?s ?p ?o . }
}";
  return $q;
}

function query_for_trips($place_uri, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT { ?s ?p ?o . } WHERE {
    ?s ?p ?o .
    ?s a as:Travel .
";
    if(!$future){
      $q .= not_in_future();
    }
    $q .= "
    { ?s as:target <$place_uri> . } UNION { ?s as:origin <$place_uri> . }
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

function query_select_albums(){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?album ?name ?descr WHERE {
  ?album a asext:Album .
  ?album as:name ?name .
  ?album as:content ?descr .
}";
  return $q;
}

function query_construct_tag_collections($uri=null){
  if($uri == null){
    $uri = "?post";
  }else{
    $uri = "<".$uri.">";
  }
  $q = get_prefixes();
  $q .= "CONSTRUCT {
    ?tag a as:Collection .
    ?tag as:items $uri .
  } WHERE {
  $uri as:tag ?tag .
  }";
  return $q;
}

function query_construct_albums(){
  $q = get_prefixes();
  $q .= "CONSTRUCT {
  ?album a asext:Album .
  ?album as:updated ?updated .
  ?album as:items ?items .
  ?album as:name ?name .
  ?album as:content ?content .
  ?album as:image ?image .
  ?album as:tag ?tag .
} WHERE {
  ?album a asext:Album .
  ?album as:updated ?updated .
  OPTIONAL { ?album as:image ?image . }
  OPTIONAL { ?album as:name ?name . }
  OPTIONAL { ?album as:content ?content . }
  OPTIONAL { ?album as:tag ?tag . }
  ?add as:object ?items .
  ?add as:target ?album .
}
ORDER BY DESC(?updated)
  ";
  return $q;
}

function query_construct_adds($collection_uri, $future=False){
  $q = get_prefixes();
  $q .= "CONSTRUCT {
  ?add ?p ?o .
} WHERE {
  ?add as:target <$collection_uri> .
  ?add as:published ?published .
  ?add ?p ?o .\n";
  if(!$future){
    $q .= "  FILTER ( ?published <= ".datenow()." ) .";
  }
  $q .= "}
ORDER BY ASC(?published)";
  return $q;
}

function query_select_wordcount($startdate=null, $enddate=null){

  if($startdate == null){
    $beg = new DateTime("10 August 1990");
    $startdate = $beg->format(DATE_ATOM);
  }
  if($enddate == null){
    $now = new DateTime();
    $enddate = $now->format(DATE_ATOM);
  }

  $q = get_prefixes();
  $q .= "SELECT ?p ?d ?wc WHERE {
  ?p a asext:Write .
  ?p asext:wordCount ?wc .
  ?p as:published ?d .
  FILTER(?d >= \"$startdate\") .
  FILTER(?d <= \"$enddate\") .
}";
  return $q;
}

function query_select_last_time_at($location){
  $q = get_prefixes();
  $q .= "SELECT ?p ?d WHERE {
  ?p a as:Arrive .
  ?p as:published ?d .
  ?p as:location <$location> .
  FILTER ( ?d <= ".datenow()." )
}
ORDER BY DESC(?d)
LIMIT 1";
  return $q;
}

function query_select_last_time_not_at($locations){
  $q = get_prefixes();
  $q .= "SELECT ?s ?d WHERE {
  ?s a as:Arrive .
  ?s as:published ?d .
  FILTER ( ?d <= ".datenow()." )
  ?s as:location ?loc .";
  foreach($locations as $location){
    $q .= "FILTER(?loc != <$location>)";
  }
  $q .= "
}
ORDER BY DESC(?d)
LIMIT 1";
  return $q;
}

/*
Select any number of return variables
vars = predicate => ?var
vals = predicate => "value"
sort is a var not a predicate
Note: limit only limits triples returned, not values by subject.
*/
function query_select_vars($vars, $vals, $limit=0, $sort=null, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $q = get_prefixes();
  $q .= "SELECT ?s ?".implode($vars, ' ?')." WHERE {
  GRAPH <$graph> {\n";
  foreach($vars as $predicate => $var){
    $q .= "?s $predicate ?$var .\n";
  }
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "  }
} ";
  if(isset($sort)){
    $q .= "\nORDER BY DESC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

/*
Construct arbitrary return variables.
See notes above
*/
function query_construct_vars($vars, $vals, $limit=0, $sort=null, $graph="https://blog.rhiaro.co.uk/", $future=False){
  $limit = $limit * count($vars);
  $q = get_prefixes();
  $q .= "CONSTRUCT { ";
  foreach($vars as $predicate => $var){
    $q .= "?s $predicate ?$var .\n";
  }
  $q .= "} WHERE {\n
    GRAPH <$graph> {\n
      ?s ?p ?o . \n";
  foreach($vars as $predicate => $var){
    $q .= "?s $predicate ?$var .\n";
  }
  foreach($vals as $predicate => $val){
    $q .= "?s $predicate $val .\n";
  }
  if(!$future){
    $q .= not_in_future();
  }
  $q .= "  }\n
} ";
  if(isset($sort)){
    $q .= "\nORDER BY DESC(?$sort)";
  }
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}


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

function query_delete_objects($uri, $p, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "DELETE FROM <$graph> { <$uri> $p ?o . }";
  return $q;
}

function query_insert_lit($s, $p, $o, $type=null, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { <$s> $p \"\"\"$o\"\"\"";
  if($type != null){
    $q .= "^^".$type;
  }
  $q .= " . }";
  return $q;
}

function query_insert_uri($s, $p, $o, $graph="https://blog.rhiaro.co.uk/"){
  $q = get_prefixes();
  $q .= "INSERT INTO <$graph> { <$s> $p <$o> . }";
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