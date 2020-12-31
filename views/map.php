<?
  if(get_value($resource, 'as:latitude') && get_value($resource, 'as:longitude')){
    $lat = get_value($resource, 'as:latitude');
    $lon = get_value($resource, 'as:longitude');
  }else{

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
  }

  $map = lat_lon_to_map($lat, $lon, 7);
?>

<div class="map">
  <img class="w1of2" src="<?=$map?>" />
  <div class="w1of2">
    <?if(isset($dbpedia)):?>
      <p><?=$dbpedia->get($location, 'foaf:name') ? $dbpedia->get($location, 'foaf:name')->getValue() : str_replace("http://dbpedia.org/resource/", "", $location)?></p>
    <?endif?>
    <?if(isset($lon)):?><p>Longitude: <?=$lon?></p><?endif?>
    <?if(isset($lat)):?><p>Latitude: <?=$lat?></p><?endif?>
    <?if(get_value($resource, "as:summary")):?><p><?=get_value($resource, "as:summary")?></p><?endif?>
    <?if(get_value($resource, "as:content")):?><p><?=get_value($resource, "as:content")?></p><?endif?>
    <?if(get_value($resource, "owl:sameAs")):?><p><a target="_blank" href="<?=get_value($resource, "owl:sameAs")?>">more info</a></p><?endif?>
  </div>

</div>
<? include 'tags.php'; ?>