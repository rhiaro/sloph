<?
$date = new DateTime(get_value($resource, 'as:published'));
?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <div>
    <?if(get_value($resource, 'as:image')):?>
      <img src="<?=get_uri(get_value($resource, 'as:image'))?>" />
    <?else:?>
      <?if(has_type($resource, 'asext:Acquire')):?>
        <span>&#128717;</span>
      <?else:?>
        <span>&#128523;</span>
      <?endif?>
    <?endif?>
    <?=get_value($resource, 'as:name') ? "<p>".get_value($resource, 'as:name')."</p>" : "" ?>
    <?=get_value($resource, 'as:summary') ? "<p>".get_value($resource, 'as:summary')."</p>" : "" ?>
    <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
    <?=get_value($resource, 'asext:cost') ? "<p><strong>".get_value($resource, 'asext:cost')."</strong></p>" : "" ?>
  </div>

  <? include('tags.php'); ?>
  
</article>