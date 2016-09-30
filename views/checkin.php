<?
  if(!isset($locations)){
    $locations = get_locations($ep);
  }
  $location_uri = get_value($resource, 'as:location');
  $locations = $locations->toRdfPhp();
  $location = array($location_uri => $locations[$location_uri]);

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
?>
<article>
  <?if(get_value($location, 'blog:pastLabel')):?>
    <p> 
      rhiaro <a href="<?=get_value($resource, 'as:location')?>"><?=isset($nextdate) ? get_value($location, 'blog:pastLabel') : get_value($location, 'blog:presentLabel')?></a> for 
      <?=($duration->y > 0) ? $duration->y . " years, " : ""?>
      <?=($duration->m > 0) ? $duration->m . " months, " : ""?>
      <?=($duration->d > 0) ? $duration->d . " days, " : ""?>
      <?=($duration->h > 0) ? $duration->h . " hours, " : ""?>
      <?=($duration->i > 0) ? $duration->i . " minutes, " : ""?>
      <?=($duration->s > 0) ? " and ".$duration->s." seconds" : ""?>
    </p>
      from <?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?> 
      to <?=isset($nextdate) ? $nextdate->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F") : "now"?> 

  <?else:?>
    <? include 'map.php'; ?>
  <?endif?>

</article>