<article>

  <?if($resource->isA('as:Object') || $resource->isA('as:Event') || $resource->isA('as:Place') || $resource->isA('as:Article')):?>
    <h1><?=$resource->get('as:name')?></h1>
  <?endif?>
  
  <?=$resource->get('as:summary')?>
  <?=$resource->get('as:content')?>
  
  <? include('tags.php'); ?>
  <?
  $date = new DateTime($resource->get('as:published'));
  ?>
  <p><time><a href="<?=str_replace("https://rhiaro.co.uk", "", $resource->getUri())?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  
</article>