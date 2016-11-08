<?
if(has_type($resource, 'as:Accept') || has_type($resource, 'as:Invite')){

  if(get_value($resource, 'as:object')){
    $eventurl = get_value($resource, 'as:object');
  }elseif(get_value($resource, 'as:inReplyTo')){
    $eventurl = get_value($resource, 'as:inReplyTo');
  }
  $event = get($ep, $eventurl);
  $event = $event['content'];
  // var_dump($event);
  if(gettype($event) != "string"){
    $event = $event->toRdfPhp();
    
    if(get_value($event, 'as:name')){ $h = get_value($event, 'as:name'); }
    if(get_value($event, 'as:startTime')){ $start = new DateTime(get_value($event, 'as:startTime')); }
    if(get_value($event, 'as:endTime')){ $end = new DateTime(get_value($event, 'as:endTime')); }
    if(get_value($event, 'as:location')){ $location = get_value($event, 'as:location'); }
  }else{
    $event = false;
  }

}elseif(has_type($resource, 'as:Event')){
  $eventurl = get_uri($resource);
  $event = $resource;
}


if(!isset($h)){ $h = get_value($resource, 'as:name'); }
if(!isset($start)){ $start = new DateTime(get_value($resource, 'as:startTime')); }
if(!isset($end)){ $end = new DateTime(get_value($resource, 'as:endTime')); }
if(!isset($location)){ $location = get_value($resource, 'as:location'); }

$date = new DateTime(get_value($resource, 'as:published'));
?>

<article>

  <h1><?=$h?></h1>
  <h2><strong>&#9745; Attending!</strong></h2>

  <aside>
    <h3>When?</h3>
    <time>
      <div>
        <span><?=$start->format("M")?></span>
        <span><?=$start->format("d")?></span>
        <span><?=$start->format("Y")?></span>
      </div>
      <span><?=$start->format("l")?></span>
      <span><?=$start->format("H:i")?> - <?=$end->format("H:i (T)")?></span>
    </time>
    <time>
      <div>
        <span><?=$end->format("M")?></span>
        <span><?=$end->format("d")?></span>
        <span><?=$end->format("Y")?></span>
      </div>
      <span><?=$end->format("l")?></span>
      <span><?=$start->format("H:i")?> - <?=$end->format("H:i (T)")?></span>
    </time>
    <h3>Where?</h3>
    <p><?=$location?></p>
    <code>// TODO: Map</code>
  </aside>

  <p><a href="<?=$eventurl?>">Event website</a></p>

  <?if($event):?>
    <?=get_value($event, 'as:summary')?>
    <?=get_value($event, 'as:content')?>
  <?endif?>

  <h3>RSVP</h3>
  <?=get_value($resource, 'as:name')?>
  <?=get_value($resource, 'as:summary')?>
  <?=get_value($resource, 'as:content')?>
  <p><time><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("D j M Y g:ia (e)")?></a></time></p>


  <? include('tags.php'); ?>
  

</article>