<?
$date = new DateTime(get_value($resource, 'as:published'));
$objects = get_values($resource, 'as:object');
?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <h2><span>&#128449;</span> <?=count($objects)?> photos added to album <a href="<?=get_value($resource, 'as:target')?>"><?=get_value($resource, 'as:target')?></a>.</h2>
  <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
  <div class="w1of1 clearfix">
    <?foreach($objects as $item):?>
      <img class="w1of5" src="<?=$item?>" alt="<?=$item?>" />
    <?endforeach?>
  </div>

  <? include('tags.php'); ?>
  
</article>