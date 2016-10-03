<?foreach($resource as $uri => $resource):?>
  <? $type = get_value(array($uri=>$resource),  'rdf:type');
     $type = EasyRdf_Namespace::shorten($type);
  ?>
  <p><span><?=get_icon_from_type($type)?></span> 
  <?=$type?>
  <?if($type == 'asext:Consume'):?>
    The last thing I ate was <?=get_value(array($uri=>$resource),  'as:name')?>, 
  <?elseif($type == 'asext:Acquire'):?>
    The last thing I acquired was <?=get_value(array($uri=>$resource),  'as:summary')?>, 
  <?elseif($type == 'as:Arrive'):?>
    <?if(get_value($locations, 'blog:presentLabel', get_value(array($uri=>$resource),  'as:location'))):?>
    I am <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=get_value($locations, 'blog:presentLabel', get_value(array($uri=>$resource),  'as:location'))?></a> since 
    <?else:?>
    I checked into <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=get_value(array($uri=>$resource),  'as:location')?></a>, 
    <?endif?>
  <?elseif($type == 'as:Like'):?>
  The last thing I liked was <a href="<?=get_value(array($uri=>$resource),  'as:object')?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif($type == 'as:Add'):?>
  The last thing I saved was <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a> to <?=get_value(array($uri=>$resource),  'as:target')?>
  <?elseif($type == 'as:Announce'):?>
  The last thing I reposted was <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif($type == 'as:Note'):?>
    The last thing I scribbled was about <em><?=$tags[get_value(array($uri=>$resource),  'as:tag')]["name"]?></em>,
  <?elseif($type == 'as:Article'):?>
  The last article I wrote was <strong><?=get_value(array($uri=>$resource),  'as:name')?></strong>
  <?elseif($type == 'as:Travel'):?>
  The last trip I planned was from <?=get_name($ep, get_value(array($uri=>$resource),  'as:origin'))?> on <?=get_value(array($uri=>$resource),  'as:startTime')?> to <?=get_name($ep, get_value(array($uri=>$resource),  'as:target'))?> at <?=get_value(array($uri=>$resource),  'as:endTime')?> 
  <?elseif($type == 'as:Accept'):?>
    <?
      $event = get($ep, get_value(array($uri=>$resource),  'as:object'));
      $event = $event['content'];
      $event = $event->resource(get_value(array($uri=>$resource),  'as:object'));
      if(!$event){
        $event = get($ep, get_value(array($uri=>$resource),  'as:inReplyTo'));
        $event = $event['content'];
        $event = $event->resource(get_value(array($uri=>$resource),  'as:inReplyTo'));
      }
      $start = new DateTime($event->get('as:startTime'));
      $end = new DateTime($event->get('as:endTime'));
    ?>
    The last event I RSVP'd to was <strong><?=$event->get('as:name') ? $event->get('as:name') : $event->getUri() ?></strong>, taking place from <?=$start->format("d F")?> to <?=$end->format("d F")?> at <?=get_name($ep, $event->get('as:location'))?>,  
  <?endif?>

  <?if(get_value(array($uri=>$resource),  'as:published')):?>
    <time><a href="<?=$uri?>"><?=time_ago(get_value(array($uri=>$resource),  'as:published'))?></a></time></p>
  <?endif?>
<?endforeach?>