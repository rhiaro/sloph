<?
if(!isset($nav)){
  $nav = post_nav($ep, $ns, $resource);
}
?>

<nav>
  <a href="/"><img src="https://rhiaro.co.uk/stash/dp.png" alt="profile" /></a>

  <?if($nav["next"]):?>
    <p><a class="right" href="<?=$nav["next"]?>">Next</a></p>
  <?endif?>
  <?if($nav["prev"]):?>
    <p><a class="left" href="<?=$nav["prev"]?>">Prev</a></p>
  <?endif?>
  <?if(isset($nav["prevtype"])):?>
    <?foreach($nav["prevtype"] as $type => $prev_one):?>
      <p><a class="left" href="<?=str_replace("https://rhiaro.co.uk", "", $prev_one)?>">Prev <?=get_icon_from_type($type)?></a></p>
    <?endforeach?>
  <?endif?>
  <?if(isset($nav["nexttype"])):?>
    <?foreach($nav["nexttype"] as $type => $next_one):?>
      <p><a class="right" href="<?=str_replace("https://rhiaro.co.uk", "", $next_one)?>">Next <?=get_icon_from_type($type)?></a></p>
    <?endforeach?>
  <?endif?>
</nav>