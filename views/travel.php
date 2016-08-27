<?
  $date = new DateTime($resource->get('as:published'));

  // TODO: check my own store first
  $dbpedia = new EasyRdf_Graph();
  $dbpedia->load($resource->get('as:origin')->getUri());
  $dbpedia->load($resource->get('as:target')->getUri());

  $from_name = $dbpedia->get($resource->get('as:origin'), 'foaf:name')->getValue();
  $from_date = new DateTime($resource->get('as:startTime'));
  $from_lat = $dbpedia->get($resource->get('as:origin'), 'geo:lat')->getValue();
  $from_lon = $dbpedia->get($resource->get('as:origin'), 'geo:long')->getValue();
  $from_map = lat_lon_to_map($from_lat, $from_lon);
  $to_name = $dbpedia->get($resource->get('as:target'), 'foaf:name')->getValue();
  $to_date = new DateTime($resource->get('as:endTime'));
  $to_lat = $dbpedia->get($resource->get('as:target'), 'geo:lat')->getValue();
  $to_lon = $dbpedia->get($resource->get('as:target'), 'geo:long')->getValue();
  $to_map = lat_lon_to_map($to_lat, $to_lon);

?>

<article>
  <h1><?=$resource->get('as:name') ? $resource->get('as:name') : "Travel plan"?></h1>
  <p>Made on 
    <datetime>
      <a href="<?=str_replace("https://rhiaro.co.uk/", "", $resource->getUri())?>"><?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?></a>
    </datetime>
    <?=$resource->get('asext:cost') ? " at a cost of ".$resource->get('asext:cost') : ""?>
  </p>
  <? include('tags.php'); ?>

  <div class="map">
    <div class="map-holder">
      <div style="background-image: url('<?=prev_tile_x($from_map)?>')"></div>
      <div style="background-image: url('<?=$from_map?>')"></div>
      <div style="background-image: url('<?=next_tile_x($from_map)?>')"></div>
    </div>
    <p>Leaving <a href="<?=$resource->get('as:origin')?>"><?=$from_name?></a> at <?=$from_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?></p>
  </div>
  <div class="map">
    <div class="map-holder">
      <div style="background-image: url('<?=prev_tile_x($to_map)?>')"></div>
      <div style="background-image: url('<?=$to_map?>')"></div>
      <div style="background-image: url('<?=next_tile_x($to_map)?>')"></div>
    </div>
    <p>Arriving in <a href="<?=$resource->get('as:target')?>"><?=$to_name?></a> at <?=$to_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?></p>
  </div>
  <hr/>
  <p class="arrow"><?=get_travel_icon_from_tags($resource->all('as:tag'))?></p>

  <?=$resource->get('as:summary')?>
  <?=$resource->get('as:content')?>

</article>