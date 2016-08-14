<article>
  <?if(in_array("as:Article", $resource->types())):?>
    <h1><?=$resource->get('as:name')?></h1>
  <?endif?>
  <?=$resource->get('as:content')?>
  <p><em><?=var_dump($resource->types())?></em></p>
</article>