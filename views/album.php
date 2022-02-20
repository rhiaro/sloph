<article class="h-entry" typeof="as:Article" about="">
  <h1><?=get_value($resource, "as:name")?></h1>
  <?=get_value($resource, "as:content")?>
  <?if(get_value($resource, "as:totalItems")):?>
    <p><em>Contains <?=get_value($resource, "as:totalItems")?> photos, the last of which were added <?=time_ago(get_value($resource, "as:updated"))?>.</em></p>
  <?else:?>
    <p class="w1of1"><a href="https://rhiaro.co.uk/photos?album=<?=$uri?>"><img src="<?=get_value($resource, "as:image")?>" /></a></p>
    <p><a href="https://rhiaro.co.uk/photos?album=<?=$uri?>">Last updated <?=time_ago(get_value($resource, "as:updated"))?></a></p>
  <?endif?>
  <?foreach($resource as $uri => $data):?>
    <?if(has_type(array($uri => $data), "as:Add")):?>
      <? $date = new DateTime(get_value(array($uri => $data), "as:published")); ?>
      <p><time><a href="<?=$uri?>"><?=$date->format("l \\t\h\\e jS \o\\f F Y \a\\t g:ia (e)")?></a></time></p>
      <?=get_value(array($uri => $data), "as:content")?>
      <div class="photos-holder">
        <?foreach(get_values(array($uri => $data), "as:object") as $item):?>
          <img src="<?=$_IMG?>200/0/<?=$item?>" />
        <?endforeach?>
      </div>
    <?endif?>
  <?endforeach?>

</article>