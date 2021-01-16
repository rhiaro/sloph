<?
$items = get_values($resource, 'as:items', $content->getUri());

if($items == null){
  $items = array();
}
$itemsh = count($items);

if(get_value($resource, 'as:partOf', $content->getUri())){
  $collection_uri = get_value($resource, 'as:partOf', $content->getUri());
  $collection_g = array($collection_uri => $resource[$collection_uri]);
  $itemsh .= " out of ".get_value($collection_g, 'as:totalItems', $collection_uri);
  $collection = array();
  foreach($items as $item){
    $collection[$item] = $resource[$item];
  }
}
?>
<h1><?=get_value($resource, 'as:name')?> (<?=$itemsh?>)</h1>
<ul>
  <?
  if(isset($collection)){
    $sorted = $collection;
    unset($sorted[key($resource)]);
  }else{
    $sorted = get_and_sort($ep, $items);
  }
  $prev_date = false;
  foreach($sorted as $uri => $resource){
    $resource = array($uri => $resource);

    include 'views/'.view_router($resource).'.php';
    $prev_date = new DateTime(get_value($resource, 'as:published'));
  }

  ?>
</ul>
