<p><span><?=get_icon($resource)?></span> 
  <?if($resource->isA('asext:Consume')):?>
  The last thing I ate was <?=$resource->get('as:name')?>, <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
  <?elseif($resource->isA('asext:Acquire')):?>
  The last thing I acquired was <?=$resource->get('as:summary')?>, <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
  <?elseif($resource->isA('as:Arrive')):?>
    <?if($locations->get($resource->get('as:location'), 'blog:presentLabel')):?>
    I am <a href="<?=$resource->get('as:location')?>"><?=$locations->get($resource->get('as:location'), 'blog:presentLabel')?></a> since <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
    <?else:?>
    I checked into <a href="<?=$resource->get('as:location')?>"><?=$resource->get('as:location')?></a>, <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
    <?endif?>
  <?elseif($resource->isA('as:Like')):?>
  The last thing I liked was <a href="<?=$resource->getUri()?>"><?=$resource->get('as:name') ? $resource->get('as:name') : $resource->get('as:object')?></a>
  <?elseif($resource->isA('as:Add')):?>
  The last thing I saved was <a href="<?=$resource->getUri()?>"><?=$resource->get('as:name') ? $resource->get('as:name') : $resource->get('as:object')?></a> to <?=$resource->get('as:target')?>
  <?elseif($resource->isA('as:Announce')):?>
  The last thing I reposted was <a href="<?=$resource->getUri()?>"><?=$resource->get('as:name') ? $resource->get('as:name') : $resource->get('as:object')?></a>
  <?elseif($resource->isA('as:Note')):?>
    The last thing I scribbled, <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>, was about <?=$resource->get('as:tag')?>
  <?elseif($resource->isA('as:Article')):?>
  The last article I wrote was <strong><?=$resource->get('as:name')?></strong> <time><a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a></time>
  <?elseif($resource->isA('as:Travel')):?>
  The last trip I planned was from <?=get_name($ep, $resource->get('as:origin'))?> on <?=$resource->get('as:startTime')?> to <?=get_name($ep, $resource->get('as:target'))?> at <?=$resource->get('as:endTime')?> <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
  <?elseif($resource->isA('as:Accept')):?>
    <?
      $event = get($ep, $resource->get('as:object'));
      $event = $event['content'];
      $event = $event->resource($resource->get('as:object'));
      if(!$event){
        $event = get($ep, $resource->get('as:inReplyTo'));
        $event = $event['content'];
        $event = $event->resource($resource->get('as:inReplyTo'));
      }
      $start = new DateTime($event->get('as:startTime'));
      $end = new DateTime($event->get('as:endTime'));
    ?>
    The last event I RSVP'd to was <strong><?=$event->get('as:name') ? $event->get('as:name') : $event->getUri() ?></strong>, taking place from <?=$start->format("d F")?> to <?=$end->format("d F")?> at <?=get_name($ep, $event->get('as:location'))?>,  <a href="<?=$resource->getUri()?>"><?=time_ago($resource->get('as:published'))?></a>
  <?endif?>
</p>