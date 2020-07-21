<?
$date = new DateTime(get_value($resource, 'as:published'));
$amounts = array();
if(get_value($resource, 'asext:amountEur')){
  $amounts[] = "&euro;".get_value($resource, 'asext:amountEur');
}
if(get_value($resource, 'asext:amountUsd')){
  $amounts[] = "&dollar;".get_value($resource, 'asext:amountUsd');
}
if(get_value($resource, 'asext:amountGbp')){
  $amounts[] = "&pound;".get_value($resource, 'asext:amountGbp');
}
if(!empty($amounts)){
  $amounts = implode(" / ", $amounts);
}
$coststring = get_value($resource, 'asext:cost');
if(get_value($resource, 'asext:expensedTo')){
  $coststring = "<del>".$coststring."</del> (expensed)";
}

$show_date = true;
if(isset($prev_date)){
  // in a collection listing
  if($prev_date && $date->format("Y-m-d") == $prev_date->format("Y-m-d")){
    $show_date = false;
  }
}
?>
<article>
    <?if($show_date):?>
      <?if(isset($prev_date)):?>
        <h2><time datetime="<?=$date->format(DATE_ATOM)?>"><?=$date->format("l \\t\h\\e jS \o\\f F")?></time></h2>
      <?else:?>
        <p><time class="dt-published"><a property="as:published" class="u-url" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("D jS \o\\f F \a\\t g:ia (e)")?></a></time></p>
      <?endif?>
    <?endif?>
    <div class="stuffholder">
      <?if(isset($prev_date)):?>
        <time datetime="<?=$date->format(DATE_ATOM)?>" title="<?=$date->format(DATE_ATOM)?>"><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("g:ia")?></a></time>
      <?endif?>
      <p class="desc"><?=get_value($resource, 'as:content') ? get_value($resource, 'as:content') : "" ?></p>
      <p class="cost">
        <?=get_value($resource, 'asext:cost') ? "<strong>$coststring</strong>" : "" ?>
        <?=$amounts ? '<span class="wee">('.$amounts.')</span>' : "" ?>
      </p>
    </div>
    <?if(get_value($resource, 'as:image')):?>
      <img src="<?=get_value($resource, 'as:image')?>" />
    <?endif?>
  <? include('tags.php'); ?>

  <?if(get_value($resource, 'as:generator')):?>
    <p class="wee"><em>Post created with </em><a property="as:generator" href="<?=get_value($resource, 'as:generator')?>"><?=get_value($resource, 'as:generator')?></a></p>
  <?endif?>

</article>