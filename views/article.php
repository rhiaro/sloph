<article>
  <?if(has_type($resource, 'as:Object') || has_type($resource, 'as:Event') || has_type($resource, 'as:Place') || has_type($resource, 'as:Article')):?>
    <h1><?=get_value($resource, 'as:name')?></h1>
  <?endif?>
  
  <?=get_value($resource, 'as:summary')?>
  <?=get_value($resource, 'as:content')?>
  
  <? //include('tags.php'); ?>
  <?if(get_value($resource, 'as:published')):?>
    <? $date = new DateTime(get_value($resource, 'as:published')); ?>
    <p><time><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>

  <?if(get_value($resource, 'as:updated')):?>
    <? $date = new DateTime(get_value($resource, 'as:updated')); ?>
    <p><em>Last modified: </em><time><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>
  
</article>