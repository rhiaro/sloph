<?
  $date = new DateTime(get_value($resource, 'as:published'));
  $origin = get_value($resource, 'as:origin');
  $target = get_value($resource, 'as:target');

  $q_origin = query_construct_uri_graph($origin, "https://rhiaro.co.uk/places/");
  $q_target = query_construct_uri_graph($target, "https://rhiaro.co.uk/places/");
  $origin_res = execute_query($ep, $q_origin);
  $target_res = execute_query($ep, $q_target);

  $dbpedia = new EasyRdf_Graph();
  $err = false;

  if(!empty($origin_res)){
    $from_name = get_value($origin_res, "as:name");
    $from_lat = get_value($origin_res, "as:latitude");
    $from_lng = get_value($origin_res, "as:longitude");
  }else{
    try{
      $dbpedia->load($origin);
      if($dbpedia->get($origin, 'foaf:name')){ $from_name = $dbpedia->get($origin, 'foaf:name')->getValue(); }
      elseif($dbpedia->get($origin, 'rdfs:label')){ $from_name = $dbpedia->get($origin, 'rdfs:label')->getValue(); }
      else{ $from_name = str_replace("http://dbpedia.org/resource/", "", $origin); }
      if($dbpedia->get($origin, 'geo:lat')) $from_lat = $dbpedia->get($origin, 'geo:lat')->getValue();
      else $from_lat = 0;
      if($dbpedia->get($origin, 'geo:long')) $from_lng = $dbpedia->get($origin, 'geo:long')->getValue();
      else $from_lng = 0;
    }catch(EasyRdf_Exception $e){
      $err = true;
      $from_name = str_replace("http://dbpedia.org/resource/", "", $origin);
      $from_lat = $from_lng = 0;
    }
  }

  if(!empty($target_res)){
    $to_name = get_value($target_res, "as:name");
    $to_lat = get_value($target_res, "as:latitude");
    $to_lng = get_value($target_res, "as:longitude");
  }else{
    try{
      $dbpedia->load($target);
      if($dbpedia->get($target, 'foaf:name')){ $to_name = $dbpedia->get($target, 'foaf:name')->getValue(); }
      elseif($dbpedia->get($target, 'rdfs:label')){ $to_name = $dbpedia->get($target, 'rdfs:label')->getValue(); }
      else{ $to_name = str_replace("http://dbpedia.org/resource/", "", $target); }
      if($dbpedia->get($target, 'geo:lat')) $to_lat = $dbpedia->get($target, 'geo:lat')->getValue();
      else $to_lat = 0;
      if($dbpedia->get($target, 'geo:long')) $to_lng = $dbpedia->get($target, 'geo:long')->getValue();
      else $to_lng = 0;
    }catch(EasyRdf_Exception $e){
      $err = true;
      $to_name = str_replace("http://dbpedia.org/resource/", "", $target);
      $to_lat = $from_lng = 0;
    }
  }

  $from_date = new DateTime(get_value($resource, 'as:startTime'));
  $to_date = new DateTime(get_value($resource, 'as:endTime'));
  $map = map_path(array($from_lng, $from_lat), array($to_lng, $to_lat));
?>

<article>
  <h1><?=get_value($resource, 'as:name') ? get_value($resource, 'as:name') : "Travel plan"?> <?=get_travel_icon_from_tags(get_values($resource, 'as:tag'))?></h1>

  <p>
    <?=(get_value($resource, "asext:status")) ? "<del>" : ""?>
    Leaving <a href="<?=get_value($resource, 'as:origin')?>"><?=$from_name?></a> at <?=$from_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?> and arriving in <a href="<?=get_value($resource, 'as:target')?>"><?=$to_name?></a> at <?=$to_date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?>
    <?=(get_value($resource, "asext:status")) ? "</del>" : ""?>
    <?=(get_value($resource, "asext:status")) ? " <strong>".get_value($resource, "asext:status")."!</strong>" : ""?>
  </p>
  <?=get_value($resource, 'as:content')?>
    <?=get_value($resource, 'asext:cost') ? " at a cost of ".get_value($resource, 'asext:cost') : ""?>

  <?if($err):?>
    <p class="fail"><em>Failed to get place information and map :(</em></p>
  <?endif?>
  <p class="w1of1"><img src="<?=$map?>" /></p>

  <p>
    <time>
      Plan made or recorded at <a href="<?=str_replace("https://rhiaro.co.uk/", "", get_uri($resource))?>"><?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F Y")?></a>
    </time>
  </p>
  <? include('tags.php'); ?>

</article>