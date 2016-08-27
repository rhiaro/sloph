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

function select_to_list($result, $types=array()){
  if(!is_array($types) || !isset($types) || empty($types)) { $types = false; }
  
  $list = array();

  if(count($result['variables']) == 1){
    $var = $result['variables'][0];
    foreach($result['rows'] as $row){
      if(!$types || ($types && in_array($row[$var." type"], $types))){
        $list[] = $row[$var];
      }
    }
  }else{
    return $result;
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
LIMIT 100
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

function query_select_s($limit=0){
  $q = "SELECT DISTINCT ?s WHERE {
  ?s ?p ?o .
}";
  if($limit > 0){
    $q .= "LIMIT $limit";
  }
  return $q;
}

function query_select_s_desc($limit=0){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  ?s ?p ?o .
  ?s as:published ?d .
}
ORDER BY DESC(?d)";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_s_and_type_desc($limit=0){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s ?t WHERE {
  ?s ?p ?o .
  OPTIONAL { ?s a ?t }
  ?s as:published ?d .
}
ORDER BY DESC(?d)";
  if($limit > 0){
    $q .= "\nLIMIT $limit";
  }
  return $q;
}

function query_select_all($limit){
  $q = "SELECT * WHERE {
  ?s ?p ?o .
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

function query_select_s_next($uri){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
";
  return $q;
}

function query_select_s_next_of_type($uri){
  $q = get_prefixes();

  $q .= "SELECT ?s WHERE { \n";
  $q .= "  ?s as:published ?d . \n";
  $q .= "  ?s a ?t . \n";
  $q .= "  <$uri> as:published ?d2 . \n";
  $q .= "  <$uri> a ?t . \n";
  $q .= "  FILTER ( ?d > ?d2 ) . \n";
  $q .= "}
ORDER BY ASC(?d)
LIMIT 1
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

function query_insert($turtle){
  $q = get_prefixes();
  $q .= "INSERT INTO <http://blog.rhiaro.co.uk#> { ".$turtle." }";
  return $q;
}

?>