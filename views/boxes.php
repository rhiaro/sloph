<a href="<?=$resource->getUri()?>">
  <?if($resource->isA('as:Arrive')):?>
    <div class="box" style="background-color: <?=$locations->get($resource->get('as:location'), 'view:color')?>"></div>
  <?else:?>
    <div class="box"><?=get_icon($resource)?></div>
  <?endif?>
</a>