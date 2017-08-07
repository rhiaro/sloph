<?
/************************************************************/
/* Access control                                           */
/************************************************************/
/* Graph based                                              */
/* The contents of some graphs are publicly gettable. The   */
/* rest is restricted depending on access token.            */
/* Obviously at some point these rules should be stored in  */
/* the triplestore.                                         */
/* Mostly TODO.                                             */
/************************************************************/

function public_graphs(){
  return [
     "https://blog.rhiaro.co.uk/"
    ,"https://contacts.rhiaro.co.uk/"
    ,"https://links.rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/locations/"
    ,"https://rhiaro.co.uk/"
    ,"https://rhiaro.co.uk/tags/"
  ];
}

function my_graphs(){
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
?>