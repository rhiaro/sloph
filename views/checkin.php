<?
  if(!isset($locations)){
    $locations = get_locations($ep);
  }
  $location = $locations->resource($resource->get('as:location'));
  $date = new DateTime($resource->get('as:published'));
?>
<article>
  
  <p>
    as of 
    <?=$date->format("g:i a (e) \o\\n \\t\h\\e jS \o\\f F")?> 
    rhiaro <a href="<?=$resource->get('as:location')?>"><?=$location->get('blog:pastLabel')?></a>
  </p>

</article>