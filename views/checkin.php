<?
if(!isset($locations)){
  $locations = get_locations($ep);
  $locations = $locations->toRdfPhp();
}
$location_uri = get_value($resource, 'as:location');

if(isset($locations[$location_uri])){
  $location = array($location_uri => $locations[$location_uri]);
}else{
  $location = $location_uri;
}

$date = new DateTime(get_value($resource, 'as:published'));

$n = nav($ep, $resource, "next", "as:Arrive");
if($n){
  $next = get($ep, $n["as:Arrive"]);
  $next = $next['content']->toRdfPhp();
  $nextdate = new DateTime(get_value($next, 'as:published'));
  $duration = $date->diff($nextdate);
}else{
  $duration = $date->diff(new DateTime());
}

$show_date = true;
$in_collection = false;
if(isset($prev_date)){
  // in a collection listing
  $in_collection = true;
  if($prev_date && $date->format("Y-m-d") == $prev_date->format("Y-m-d")){
    $show_date = false;
  }
}
?>
<article>
  <?if($show_date && $in_collection):?>
    <?if(isset($prev_date)):?>
      <h2><time datetime="<?=$date->format(DATE_ATOM)?>"><?=$date->format("l \\t\h\\e jS \o\\f F Y")?></time></h2>
    <?else:?>
      <p><time class="dt-published"><a property="as:published" class="u-url" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("D jS \o\\f F Y \a\\t g:ia (e)")?></a></time></p>
    <?endif?>
  <?endif?>
  <?if(isset($prev_date)):?>
    <time class="inline" datetime="<?=$date->format(DATE_ATOM)?>" title="<?=$date->format(DATE_ATOM)?>"><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("g:ia")?></a></time>
  <?endif?>
  <?if(get_value($location, 'blog:pastLabel')):?>
    <p style="display:inline">
      <a href="<?=get_value($resource, 'as:location')?>"><?=isset($nextdate) ? get_value($location, 'blog:pastLabel') : get_value($location, 'blog:presentLabel')?></a> for
      <?=($duration->y > 0) ? $duration->y . " years, " : ""?>
      <?=($duration->m > 0) ? $duration->m . " months, " : ""?>
      <?=($duration->d > 0) ? $duration->d . " days, " : ""?>
      <?=($duration->h > 0) ? $duration->h . " hours, " : ""?>
      <?=($duration->i > 0) ? $duration->i . " minutes, " : ""?>
      <?=($duration->s > 0) ? " and ".$duration->s." seconds" : ""?>
    </p>
    <?if(!$in_collection):?><p class="wee" style="color:#000;">
      from <?=$date->format("g:ia (e)")?>
      <?=(isset($nextdate) && $date->format("Y-m-d") != $nextdate->format("Y-m-d")) || !isset($nextdate) ? $date->format(" \o\\n l \\t\h\\e jS \o\\f F") : ""?>
      to <?=isset($nextdate) ? $nextdate->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y") : "now"?>
    </p><?endif?>
  <?else:?>
    <? include 'map.php'; ?>
  <?endif?>

  <?if(get_values($resource, 'as:tag')):?>

  <?endif?>

</article>