<article class="h-entry" typeof="as:Article" about="">
  <h1>Photos</h1>
  <div class="photos">
    <?foreach($resource as $uri => $album):?>
      <?if($uri != "https://rhiaro.co.uk/photos"):?>
        <a href="<?=$uri?>">
          <img src="<?=get_value(array($uri => $album), "as:image")?>" alt="Photo from the album <?=get_value(array($uri => $album), "as:name")?>" />
          <h3><?=get_value(array($uri => $album), "as:name")?></h3>
          <div class="descr">
            <?=get_value(array($uri => $album), "as:content")?>
          </div>
          <p class="count"><img src="/views/icon_camera.png" title="Number of photos" alt="Number of photos " /><?=number_format(get_value(array($uri => $album), "as:totalItems"))?></p>
          <p>Last updated: <?=time_ago(get_value(array($uri => $album), "as:updated"), "months")?></p>
        </a>
      <?endif?>
    <?endforeach?>
  </div>
</article>