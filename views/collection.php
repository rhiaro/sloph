<? $items = get_values($resource, 'as:items'); ?>
<h1><?=get_value($resource, 'as:name')?> (<?=count($items)?>)</h1>
<ul>
  <?
  foreach($items as $item){
    $r = get($ep, $item);
    $resource = $r['content'];
    $resource = $resource->toRdfPhp();
    include 'views/'.view_router($resource).'.php';
  }
  ?>
</ul>
