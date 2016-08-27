<?
// Get next resource by date
$q = query_select_s_next($resource->getUri());
$nextres = execute_query($ep, $q);
if(!empty($nextres['rows'])){
  $next = get($ep, $nextres['rows'][0]['s']);
  $next = $next['content'];
}
// Get next resource by date of the same type
$next_types = array();
foreach($resource->types() as $type){
  if($type != 'as:Activity'){ // Crude but effective.
    $q = query_select_s_next_of_type($resource->getUri(), $type);
    $nextres = execute_query($ep, $q);
    if(!empty($nextres['rows'])){
      $next_type = get($ep, $nextres['rows'][0]['s']);
      $next_types[$type] = $next_type['content'];
    }
  }
}

// Get prev resource by date
$q = query_select_s_prev($resource->getUri());
$prevres = execute_query($ep, $q);
if(!empty($prevres['rows'])){
  $prev = get($ep, $prevres['rows'][0]['s']);
  $prev = $prev['content'];
}
// Get prev resource by date of the same type
$prev_types = array();
foreach($resource->types() as $type){
  if($type != 'as:Activity'){ // Crude but effective.
    $q = query_select_s_prev_of_type($resource->getUri(), $type);
    $prevres = execute_query($ep, $q);
    if(!empty($prevres['rows'])){
      $prev_type = get($ep, $prevres['rows'][0]['s']);
      $prev_types[$type] = $prev_type['content'];
    }
  }
}
?>

<nav>
  <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev->getUri())?>">Prev</a></p>
  <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next->getUri())?>">Next</a></p>
  <?foreach($next_types as $type => $next_one):?>
    <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next_one->getUri())?>">Next <?=get_icon_from_type($type)?></a></p>
  <?endforeach?>
  <?foreach($prev_types as $type => $prev_one):?>
    <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev_one->getUri())?>">Prev <?=get_icon_from_type($type)?></a></p>
  <?endforeach?>
</nav>