<?
  if(!isset($location)){
    $location = get_value($resource, 'as:location');
  }
  try{
    $dbpedia = new EasyRdf_Graph();
    $dbpedia->load($location);
    if($dbpedia->get($location, 'geo:lat')){ // TODO: Follow dbpedia redirects :s issue #22
      $lat = $dbpedia->get($location, 'geo:lat')->getValue();
    }else{
      $lat = 0;
    }
    if($dbpedia->get($location, 'geo:long')){
      $lon = $dbpedia->get($location, 'geo:long')->getValue();
    }else{
      $lon = 0;
    }
    
  }catch(EasyRdf_Http_Exception $e){
    $lat = $lon = 0;
  }  

  $map = lat_lon_to_map($lat, $lon, 7);
?>
<p><datetime><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("l \\t\h\\e jS \o\\f F \a\\t g:ia (e)")?></a></datetime></p>
<div class="map">
  <img src="<?=$map?>" />
  <p><?=$dbpedia->get($location, 'foaf:name') ? $dbpedia->get($location, 'foaf:name')->getValue() : str_replace("http://dbpedia.org/resource/", "", $location)?></p>
  <?=get_value($resource, 'as:name') ? "<p>".get_value($resource, 'as:name')."</p>" : "" ?>
  <?=get_value($resource, 'as:summary') ? "<p>".get_value($resource, 'as:summary')."</p>" : "" ?>
  <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
</div>
<? include 'tags.php'; ?>