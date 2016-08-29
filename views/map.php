<?
  $dbpedia = new EasyRdf_Graph();
  $dbpedia->load($resource->get('as:location')->getUri());
  $lat = $dbpedia->get($resource->get('as:location'), 'geo:lat')->getValue();
  $lon = $dbpedia->get($resource->get('as:location'), 'geo:long')->getValue();
  $map = lat_lon_to_map($lat, $lon, 7);
?>
<p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", $resource->getUri())?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
<div>
  <img src="<?=$map?>" />
  <p><?=$dbpedia->get($resource->get('as:location'), 'foaf:name')->getValue()?></p>
  <?=$resource->get('as:name') ? "<p>".$resource->get('as:name')."</p>" : "" ?>
  <?=$resource->get('as:summary') ? "<p>".$resource->get('as:summary')."</p>" : "" ?>
  <?=$resource->get('as:content') ? "<p>".$resource->get('as:content')."</p>" : "" ?>
</div>
<? include 'tags.php'; ?>