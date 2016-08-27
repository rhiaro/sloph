<?
$date = new DateTime($resource->get('as:published'));
?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", $resource->getUri())?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <div>
    <?if($resource->get('as:image')):?>
      <img src="<?=$resource->get('as:image')->getUri()?>" />
    <?else:?>
      <?if($resource->isA('asext:Acquire')):?>
        <span>&#128717;</span>
      <?else:?>
        <span>&#128523;</span>
      <?endif?>
    <?endif?>
    <?=$resource->get('as:name') ? "<p>".$resource->get('as:name')."</p>" : "" ?>
    <?=$resource->get('as:summary') ? "<p>".$resource->get('as:summary')."</p>" : "" ?>
    <?=$resource->get('as:content') ? "<p>".$resource->get('as:content')."</p>" : "" ?>
    <?=$resource->get('asext:cost') ? "<p><strong>".$resource->get('asext:cost')."</strong></p>" : "" ?>
  </div>

  <? include('tags.php'); ?>
  
</article>