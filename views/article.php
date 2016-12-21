<article class="h-entry">
  <?if(has_type($resource, 'as:Object') || has_type($resource, 'as:Event') || has_type($resource, 'as:Place') || has_type($resource, 'as:Article')):?>
    <h1 class="p-name"><?=get_value($resource, 'as:name')?></h1>
  <?endif?>

  <?if(get_value($resource, 'as:inReplyTo')):?>
    <p><em>@ <a class="u-in-reply-to" href="<?=get_value($resource, 'as:inReplyTo')?>">In reply to this</a>:</em></p>
  <?endif?>
  
  <div class="p-summary" style="display: none"><?=get_value($resource, 'as:summary')?></div>
  <div class="e-content"><?=get_value($resource, 'as:content')?></div>
  
  <? include('tags.php'); ?>
  <?if(get_value($resource, 'as:published')):?>
    <? $date = new DateTime(get_value($resource, 'as:published')); ?>
    <p><time class="dt-published"><a class="u-url" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>

  <?if(get_value($resource, 'as:updated')):?>
    <? $date = new DateTime(get_value($resource, 'as:updated')); ?>
    <p><em>Last modified: </em><time class="dt-updated"><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>
  
</article>