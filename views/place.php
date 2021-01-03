<?
if(!isset($locations)){
  $locations = get_locations($ep);
  $locations = $locations->toRdfPhp();
}

$q_trips = query_for_trips(get_uri($resource));
$trips = execute_query($ep, $q_trips);
?>
<article>
  <h1 class="p-name" property="as:name"><?=get_value($resource, 'as:name')?></h1>
  <div class="p-summary" property="as:summary" style="display: none"><?=get_value($resource, 'as:summary')?></div>
  <div class="e-content" property="as:content"><?=get_value($resource, 'as:content')?></div>
  <?if(!array_key_exists(get_uri($resource), $locations)):?>
    <? include 'map.php'; ?>
    <?if(!empty($trips)):?>
    <div class="w1of1">
      <h2>Trips</h2>
      <p>I logged the following journeys to <?=get_value($resource, 'as:name')?>:</p>
      <ul>
        <?foreach($trips as $uri => $trip):?>
          <?
          $tripdate = new DateTime(get_value(array($uri=>$trip), "as:endTime"));
          ?>
          <li><a href="<?=$uri?>"><?=get_value(array($uri=>$trip), "as:name") ? get_value(array($uri=>$trip), "as:name") : "Travel plan"?></a> <em class="wee"><?=$tripdate->format("M Y")?><?if(get_value(array($uri=>$trip), "as:summary")):?>: <?=get_value(array($uri=>$trip), "as:summary")?><?endif?></em></li>
        <?endforeach?>
      </ul>
    </div>
    <?endif?>
  <?endif?>
  <?if(get_values($resource, 'as:tag')):?>

  <?endif?>

</article>