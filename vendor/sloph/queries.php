<?

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

function query_select_s($limit){
  $q = "SELECT DISTINCT ?s WHERE {
  ?s ?p ?o .
}
LIMIT $limit
";
  return $q;
}

function query_select_s_desc($limit){
  $q = get_prefixes();
  $q .= "SELECT DISTINCT ?s WHERE {
  ?s ?p ?o .
  ?s dct:created ?d .
}
ORDER BY DESC(?d)
LIMIT $limit
";
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

?>