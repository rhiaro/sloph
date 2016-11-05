<? $items = get_values($resource, 'as:items'); ?>
<h1><?=get_value($resource, 'as:name')?> (<?=count($items)?>)</h1>
<ul>
  <?
  $sorted = get_and_sort($ep, $items);
  foreach($sorted as $uri => $resource){
    $resource = array($uri => $resource);
    include 'views/'.view_router($resource).'.php';
  }
  
  ?>
</ul>
