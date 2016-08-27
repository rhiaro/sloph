<?
echo $resource->dump();
if($resource->isA('as:Accept') || $resource->isA('as:Invite')){

  if($resource->get('as:object')){
    $eventurl = $resource->get('as:object');
  }elseif($resource->get('as:inReplyTo')){
    $eventurl = $resource->get('as:inReplyTo');
  }
  $event = get($ep, $eventurl);
  $event = $event['content'];
  if(gettype($event) != "string"){
    $event = $event->resource($eventurl);
    
    if($event->get('as:name')){ $h = $event->get('as:name'); }
    if($event->get('as:startTime')){ $start = new DateTime($event->get('as:startTime')); }
    if($event->get('as:endTime')){ $end = new DateTime($event->get('as:endTime')); }
    if($event->get('as:location')){ $location = $event->get('as:location'); }
  }else{
    $event = false;
  }

}
if(!isset($h)){ $h = $resource->get('as:name'); }
if(!isset($start)){ $start = new DateTime($resource->get('as:startTime')); }
if(!isset($end)){ $end = new DateTime($resource->get('as:endTime')); }
if(!isset($location)){ $location = $resource->get('as:location'); }

$date = new DateTime($resource->get('as:published'));
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
    <?=$event->get('as:summary')?>
    <?=$event->get('as:content')?>
  <?endif?>

  <h3>RSVP</h3>
  <?=$resource->get('as:name')?>
  <?=$resource->get('as:summary')?>
  <?=$resource->get('as:content')?>
  <p><time><a href="<?=str_replace("https://rhiaro.co.uk", "", $resource->getUri())?>"><?=$date->format("D j M Y g:ia (e)")?></a></time></p>


  <? include('tags.php'); ?>
  

</article>