<?
$date = new DateTime($resource->get('as:published'));
$linkclass = "";
if($resource->isA('as:Like')){
  $linkclass .= " like";
}
if($resource->isA('as:Add')){
  $linkclass .= " bookmark";
}
if($resource->isA('as:Announce')){
  $linkclass .= " repost";
}
?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", $resource->getUri())?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <div>
    <p>
      <a class="object<?=$linkclass?>" href="<?=$resource->get('as:object')?>">
        <span><?=get_icon($resource)?></span>
        <?=$resource->get('as:name') ? $resource->get('as:name') : $resource->get('as:object') ?>
      </a>
    </p>

    <?=$resource->get('as:summary') ? "<p>".$resource->get('as:summary')."</p>" : "" ?>
    <?=$resource->get('as:content') ? "<p>".$resource->get('as:content')."</p>" : "" ?>
    <?=$resource->get('asext:cost') ? "<p><strong>".$resource->get('asext:cost')."</strong></p>" : "" ?>
  </div>

  <? include('tags.php'); ?>
  
</article>