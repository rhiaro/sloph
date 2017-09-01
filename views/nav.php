<?
$next = null;
$prev = null;
if(get_value($resource, $ns->expand("as:next"))){
  $next = get_value($resource, $ns->expand("as:next"));
}else{
  $next = nav($ep, $resource, "next");
  if(!empty($next)){
    $next = $next[0];
  }
}
if(get_value($resource, $ns->expand("as:prev"))){
  $prev = get_value($resource, $ns->expand("as:prev"));
}else{
  $prev = nav($ep, $resource, "prev");
  if(!empty($prev)){
    $prev = $prev[0];
  }
}
// Get next resource by date of the same type
$next_types = array();
$prev_types = array();
$this_types = get_values($resource, $ns->expand("rdf:type"));

if(is_array($this_types)){
  foreach($this_types as $type){
    $n = nav($ep, $resource, "next", $type);
    $p = nav($ep, $resource, "prev", $type);
    if($n){
      $next_types = array_merge($next_types, $n);
    }
    if($p){
      $prev_types = array_merge($prev_types, $p);
    }
  }
}
?>

<nav>
  <a href="/"><img src="https://rhiaro.co.uk/stash/dp.png" alt="profile" /></a>

  <?if($next):?>
    <p><a class="right" href="<?=$next?>">Next</a></p>
  <?endif?>
  <?if($prev):?>
    <p><a class="left" href="<?=$prev?>">Prev</a></p>
  <?endif?>

  <?foreach($prev_types as $type => $prev_one):?>
    <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev_one)?>">Prev <?=get_icon_from_type($type)?></a></p>
  <?endforeach?>
  <?foreach($next_types as $type => $next_one):?>
    <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next_one)?>">Next <?=get_icon_from_type($type)?></a></p>
  <?endforeach?>
</nav>