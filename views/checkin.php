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
    I was <a href="<?=$resource->get('as:location')?>"><?=$location->get('blog:pastLabel')?></a>
  </p>
  <p><datetime><a href="<?=str_replace("https://rhiaro.co.uk/", "", $resource->getUri())?>"><?=$resource->get('as:published')?></a></datetime></p>
  
  <p><em><?=$resource->dump()?></em></p>

</article>