<?
$date = new DateTime(get_value($resource, 'as:published'));
$amounts = array();
if(get_value($resource, 'asext:amountEur')){
  $amounts[] = get_value($resource, 'asext:amountEur')." EUR";
}
if(get_value($resource, 'asext:amountUsd')){
  $amounts[] = get_value($resource, 'asext:amountUsd')." USD";
}
if(get_value($resource, 'asext:amountGbp')){
  $amounts[] = get_value($resource, 'asext:amountGbp')." GBP";
}
if(!empty($amounts)){
  $amounts = implode(" / ", $amounts);
}
$coststring = get_value($resource, 'asext:cost');
if(get_value($resource, 'asext:expensedTo')){
  $coststring = "<del>".$coststring."</del> (expensed)";
}

?>
<article>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
  <div>
    <?if(get_value($resource, 'as:image')):?>
      <img src="<?=get_value($resource, 'as:image')?>" />
    <?else:?>
      <?if(has_type($resource, 'asext:Acquire')):?>
        <span>&#128717;</span>
      <?else:?>
        <span>&#128523;</span>
      <?endif?>
    <?endif?>
    <?=get_value($resource, 'as:name') ? "<p><strong>".get_value($resource, 'as:name')."</strong></p>" : "" ?>
    <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
    <?=get_value($resource, 'asext:cost') ? "<p><strong>$coststring</strong></p>" : "" ?>

    <?=$amounts ? '<p class="wee">('.$amounts.')</p>' : "" ?>
  </div>

  <? include('tags.php'); ?>

  <?if(get_value($resource, 'as:generator')):?>
    <p class="wee"><em>Post created with </em><a property="as:generator" href="<?=get_value($resource, 'as:generator')?>"><?=get_value($resource, 'as:generator')?></a></p>
  <?endif?>

</article>