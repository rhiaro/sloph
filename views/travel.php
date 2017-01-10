<?
  $date = new DateTime(get_value($resource, 'as:published'));
  $origin = get_value($resource, 'as:origin');
  $target = get_value($resource, 'as:target');
  // TODO: check my own store first
  $dbpedia = new EasyRdf_Graph();
  $err = false;
  try{
    $dbpedia->load($origin);
    $dbpedia->load($target);

    if($dbpedia->get($origin, 'foaf:name')){ $from_name = $dbpedia->get($origin, 'foaf:name')->getValue(); }
    elseif($dbpedia->get($origin, 'rdfs:label')){ $from_name = $dbpedia->get($origin, 'rdfs:label')->getValue(); }
    else{ $from_name = str_replace("http://dbpedia.org/resource/", "", $origin); }
    if($dbpedia->get($origin, 'geo:lat')) $from_lat = $dbpedia->get($origin, 'geo:lat')->getValue();
    else $from_lat = 0;
    if($dbpedia->get($origin, 'geo:long')) $from_lon = $dbpedia->get($origin, 'geo:long')->getValue();
    else $from_lon = 0;

    if($dbpedia->get($target, 'foaf:name')){ $to_name = $dbpedia->get($target, 'foaf:name')->getValue(); }
    elseif($dbpedia->get($target, 'rdfs:label')){ $to_name = $dbpedia->get($target, 'rdfs:label')->getValue(); }
    else{ $to_name = str_replace("http://dbpedia.org/resource/", "", $target); }
    if($dbpedia->get($target, 'geo:lat')) $to_lat = $dbpedia->get($target, 'geo:lat')->getValue();
    else $to_lat = 0;
    if($dbpedia->get($target, 'geo:long')) $to_lon = $dbpedia->get($target, 'geo:long')->getValue();
    else $to_lon = 0;
  }catch(EasyRdf_Exception $e){
    $err = true;
    $from_name = str_replace("http://dbpedia.org/resource/", "", $origin);
    $to_name = str_replace("http://dbpedia.org/resource/", "", $target);
    $from_lat = $to_lat = $from_lon = $to_lon = 0;
  }
  $from_date = new DateTime(get_value($resource, 'as:startTime'));
  $to_date = new DateTime(get_value($resource, 'as:endTime'));
  $map = map_path(array($from_lon, $from_lat), array($to_lon, $to_lat));
?>

<article>
  <h1><?=get_value($resource, 'as:name') ? get_value($resource, 'as:name') : "Travel plan"?> <?=get_travel_icon_from_tags(get_values($resource, 'as:tag'))?></h1>
  <p>Made on 
    <datetime>
      <a href="<?=str_replace("https://rhiaro.co.uk/", "", get_uri($resource))?>"><?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?></a>
    </datetime>
    <?=get_value($resource, 'asext:cost') ? " at a cost of ".get_value($resource, 'asext:cost') : ""?>
  </p>
  <? include('tags.php'); ?>

  <?if($err):?>
    <p class="fail"><em>Tried to get place information and maps from dbpedia, but could not connect :(</em></p>
  <?endif?>
  <?if(!$err):?>
    <p class="w1of1"><img src="<?=$map?>" /></p>
    <p>Leaving <a href="<?=get_value($resource, 'as:origin')?>"><?=$from_name?></a> at <?=$from_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?> and arriving in <a href="<?=get_value($resource, 'as:target')?>"><?=$to_name?></a> at <?=$to_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?></p>
  <?endif?>

  <?=get_value($resource, 'as:content')?>

</article>