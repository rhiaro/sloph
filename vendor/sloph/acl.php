<?
/************************************************************/
/* Access control                                           */
/************************************************************/
/* Graph based                                              */
/* The contents of some graphs are publicly gettable. The   */
/* rest is restricted depending on access token.            */
/* Obviously(?) at some point these rules should be stored  */
/* in the triplestore.                                      */
/* Mostly TODO.                                             */
/************************************************************/

/** Graphs **/

function public_graphs(){
  // Things in these graphs should always be visible.
  return [
     "https://blog.rhiaro.co.uk/"
    ,"https://contacts.rhiaro.co.uk/"
    ,"https://links.rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/locations/"
    ,"https://rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/tags/"
    ,"https://rhiaro.co.uk/projects/"
  ];
}

function my_graphs(){
  // Things in these graphs should show up in the feed.
  return [
     "https://blog.rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/locations/"
    ,"https://rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/tags/"
  ];
}

function my_public_graphs(){
  return array_merge(public_graphs(), my_graphs());
}

function restricted_graphs(){
  global $R_KEY;
  global $M_KEY;
  global $P_KEY;
  global $S_KEY;
  return [
     "https://rhiaro.co.uk/u/me" => [$R_KEY, $M_KEY]
    ,"https://rhiaro.co.uk/u/occrp" => [$P_KEY]
    ,"https://rhiaro.co.uk/u/sandro" => [$S_KEY]
  ];
}

function restricted_graphs_by_key(){
  // REVISIT THIS when a key access more than one graph.
  $gs = restricted_graphs();
  $keys = array();
  foreach($gs as $g => $k){
    foreach($vs as $v){
      $keys[$v] = $g;
    }
  }
  return $keys;
}

function restricted_graph_by_key($key){
  $graphs = restricted_graphs_by_key();
  return $graphs[$key];
}

/** Checks **/

function is_public($graph){
  $public_graphs = public_graphs();
  return in_array($graph, $public_graphs);
}

function is_mine($graph){
  $my_graphs = my_graphs();
  return in_array($graph, $my_graphs);
}

function public_and_mine($graph){
  return is_public($graph) && is_mine($graph);
}

/** Users **/

function is_guest($id){
  global $GUESTS;
  if(isset($GUESTS)){
    return in_array($id, $GUESTS);
  }
  return false;
}
?>