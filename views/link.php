<?
$date = new DateTime(get_value($resource, 'as:published'));
$linkclass = "";
if(has_type($resource, 'as:Like')){
  $linkclass .= " like";
}
if(has_type($resource, 'as:Add')){
  $linkclass .= " bookmark";
}
if(has_type($resource, 'as:Announce')){
  $linkclass .= " repost";
}
?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <div>
    <p>
      <a class="object<?=$linkclass?>" href="<?=get_value($resource, 'as:object')?>">
        <span><?=get_icon($resource)?></span>
        <span><?=get_value($resource, 'as:name') ? get_value($resource, 'as:name') : get_value($resource, 'as:object') ?></span>
      </a>
    </p>

    <?=get_value($resource, 'as:summary') ? "<p>".get_value($resource, 'as:summary')."</p>" : "" ?>
    <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
    <?=get_value($resource, 'asext:cost') ? "<p><strong>".get_value($resource, 'asext:cost')."</strong></p>" : "" ?>
  </div>

  <? include('tags.php'); ?>
  
</article>