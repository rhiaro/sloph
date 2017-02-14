<article class="h-entry" typeof="as:Article" about="">
  <?if(has_type($resource, 'as:Object') || has_type($resource, 'as:Event') || has_type($resource, 'as:Place') || has_type($resource, 'as:Article')):?>
    <h1 class="p-name" property="as:name"><?=get_value($resource, 'as:name')?></h1>
  <?endif?>

  <?if(get_values($resource, 'as:inReplyTo')):?>
    <p><em>In reply to:
      <?foreach(get_values($resource, 'as:inReplyTo') as $r):?>
        <a class="u-in-reply-to" rel="as:inReplyTo" href="<?=$r?>"><?=$r?></a> 
      <?endforeach?>
    </em></p>
  <?endif?>
  
  <div class="p-summary" property="as:summary" style="display: none"><?=get_value($resource, 'as:summary')?></div>
  <div class="e-content" property="as:content"><?=get_value($resource, 'as:content')?></div>
  
  <? include('tags.php'); ?>
  <?if(get_value($resource, 'as:published')):?>
    <? $date = new DateTime(get_value($resource, 'as:published')); ?>
    <p><time class="dt-published"><a property="as:published" class="u-url" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>

  <?if(get_value($resource, 'as:updated')):?>
    <? $date = new DateTime(get_value($resource, 'as:updated')); ?>
    <p><em>Last modified: </em><time class="dt-updated"><a property="as:updated" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("d M Y, H:i (e)")?></a></time></p>
  <?endif?>
  
</article>