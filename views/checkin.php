<?
  if(!isset($locations)){
    $locations = get_locations($ep);
  }
  $location = $locations->resource($resource->get('as:location'));
  $date = new DateTime($resource->get('as:published'));

  $q = query_select_s_next_of_type($resource->getUri(), 'as:Arrive');
  $nextres = execute_query($ep, $q);
  if(!empty($nextres['rows'])){
    $next = get($ep, $nextres['rows'][0]['s']);
    $next = $next['content'];
    $nextdate = new DateTime($next->get($nextres['rows'][0]['s'], 'as:published'));
    $duration = $date->diff($nextdate);
  }else{
    $duration = $date->diff(new DateTime());
  }
  var_dump($location->getUri());
?>
<article>
  <?if($location->get('blog:pastLabel')):?>
    <p> 
      rhiaro <a href="<?=$resource->get('as:location')?>"><?=isset($nextdate) ? $location->get('blog:pastLabel') : $location->get('blog:presentLabel')?></a> 
      from <?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?> 
      to <?=isset($nextdate) ? $nextdate->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F") : "now"?> 
      (
      <?=($duration->y > 0) ? $duration->y . " years, " : ""?>
      <?=($duration->m > 0) ? $duration->m . " months, " : ""?>
      <?=($duration->d > 0) ? $duration->d . " days, " : ""?>
      <?=($duration->h > 0) ? $duration->h . " hours, " : ""?>
      <?=($duration->i > 0) ? $duration->i . " minutes, " : ""?>
      <?=($duration->s > 0) ? " and ".$duration->s." seconds" : ""?>
      )
    </p>

  <?else:?>
    <? include 'map.php'; ?>
  <?endif?>

</article>