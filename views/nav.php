<?
// Get next resource by date
$q = query_select_s_next($resource->getUri());
$nextres = execute_query($ep, $q);
if(!empty($nextres['rows'])){
  $next = get($ep, $nextres['rows'][0]['s']);
  $next = $next['content'];
}
// Get next resource by date of the same type
if(!array_intersect($resource->types(), $next->types())){
  $q = query_select_s_next_of_type($resource->getUri());
  $nextres = execute_query($ep, $q);
  if(!empty($nextres['rows'])){
    $next_type = get($ep, $nextres['rows'][0]['s']);
    $next_type = $next_type['content'];
  }
}else{
  $next_type = $next;
}

// Get previous resource by date
$q = query_select_s_prev($resource->getUri());
$prevres = execute_query($ep, $q);
if(!empty($prevres['rows'])){
  $prev = get($ep, $prevres['rows'][0]['s']);
  $prev = $prev['content'];
}
// Get previous resource by date of the same type
if(!array_intersect($resource->types(), $prev->types())){
  $q = query_select_s_prev_of_type($resource->getUri());
  $prevres = execute_query($ep, $q);
  if(!empty($prevres['rows'])){
    $prev_type = get($ep, $prevres['rows'][0]['s']);
    $prev_type = $prev_type['content'];
  }
}else{
  $prev_type = $prev;
}
?>

<nav>
  <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev->getUri())?>">Prev</a></p>
  <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next->getUri())?>">Next</a></p>
  <?foreach($resource->types() as $type):?>
    <!-- FIXME: The limit on the query means even when there are multiple types it only gets one back -->
    <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev_type->getUri())?>">Prev <?=$type?></a></p>
    <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next_type->getUri())?>">Next <?=$type?></a></p>
  <?endforeach?>
</nav>