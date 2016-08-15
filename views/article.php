<article>

  <?if(empty($resource->types()) || $resource->isA('as:Event') || $resource->isA('as:Place') || $resource->isA('as:Article')):?>
    <h1><?=$resource->get('as:name')?></h1>
  <?endif?>
  
  <?=$resource->get('as:summary')?>
  <?=$resource->get('as:content')?>
  
  <p><?=$resource->join('as:tag', ", ")?></p>
  <p><datetime><a href="<?=$resource->getUri()?>"><?=$resource->get('as:published')?></a></datetime></p>
  
  <p><em><?=var_dump($resource->types())?></em></p>

</article>