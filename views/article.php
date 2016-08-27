<article>

  <?if($resource->isA('as:Object') || $resource->isA('as:Event') || $resource->isA('as:Place') || $resource->isA('as:Article')):?>
    <h1><?=$resource->get('as:name')?></h1>
  <?endif?>
  
  <?=$resource->get('as:summary')?>
  <?=$resource->get('as:content')?>
  
  <? include('tags.php'); ?>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk/", "", $resource->getUri())?>"><?=$resource->get('as:published')?></a></datetime></p>
  
  <p><em><?=$resource->dump()?></em></p>

</article>