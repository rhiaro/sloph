<h1><?=$resource->get('as:name')?> (<?=$resource->countValues('as:items')?>)</h1>
<ul>
  <?
  foreach($resource->all('as:items') as $item){
    $r = get($ep, $item->getUri());
    $resource = $r['content'];
    $resource = $resource->resource($item->getUri());
    include 'views/'.view_router($resource).'.php';
  }
  ?>
</ul>
