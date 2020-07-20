<style type="text/css">
  article a {
    text-decoration: none;
  }
  div {
    display: flex;
    align-items: stretch;
  }
  time span {
    background-color: #470229;
    color: #fff;
    font-weight: bold;
    padding: 0.4em;
    display: inline;
    line-height: 2;
  }
  img {
    border: 2px solid #470229;
  }
  div p.desc{
    flex-grow: 4;
  }
  div p {
    margin: 0;
    padding-left: 0.4em;
  }
  div p.cost {

  }
</style>
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
  if($date->format("Y-m-d") == $prev_date->format("Y-m-d")){
    $show_date = false;
  }
}
?>
<article>
  <a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>">
    <?if($show_date):?>
      <h2><time datetime="<?=$date->format(DATE_ATOM)?>"><?=$date->format("l \\t\h\\e jS \o\\f F")?></time></h2>
    <?endif?>
    <div>
      <time datetime="<?=$date->format(DATE_ATOM)?>" title="<?=$date->format(DATE_ATOM)?>"><span><?=$date->format("g:ia")?></span></time>
      <p class="desc"><?=get_value($resource, 'as:content') ? get_value($resource, 'as:content') : "" ?></p>
      <p class="cost">
        <?=get_value($resource, 'asext:cost') ? "<strong>$coststring</strong>" : "" ?>
        <?=$amounts ? '<span class="wee">('.$amounts.')</span>' : "" ?>
      </p>
    </div>
      <?if(get_value($resource, 'as:image')):?>
        <img src="<?=get_value($resource, 'as:image')?>" />
      <?endif?>
    </a>
  <? include('tags.php'); ?>

  <?if(get_value($resource, 'as:generator')):?>
    <p class="wee"><em>Post created with </em><a property="as:generator" href="<?=get_value($resource, 'as:generator')?>"><?=get_value($resource, 'as:generator')?></a></p>
  <?endif?>

</article>