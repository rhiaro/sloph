<h1><?=$resource->get('as:name')?></h1>
<ul>
  <?foreach($resource->all('as:items') as $item):?>
    <li><a href="<?=$item->getUri()?>"><?=$item->getUri()?></a></li>
  <?endforeach?>
</ul>
