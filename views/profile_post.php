<?foreach($resource as $uri => $resource):?>
  <p><span><?=get_icon_from_type(get_value(array($uri=>$resource),  'rdf:type'))?></span> 
  <?if(has_type(array($uri=>$resource),  'asext:Consume')):?>
    The last thing I ate was <?=get_value(array($uri=>$resource),  'as:name')?>, 
  <?elseif(has_type(array($uri=>$resource),  'asext:Acquire')):?>
    The last thing I acquired was <?=get_value(array($uri=>$resource),  'as:summary')?>, 
  <?elseif(has_type(array($uri=>$resource),  'as:Arrive')):?>
    <?if(get_value($locations, get_value(array($uri=>$resource),  'as:location'), 'blog:presentLabel')):?>
    I am <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=$locations->get(get_value(array($uri=>$resource),  'as:location'), 'blog:presentLabel')?></a> since 
    <?else:?>
    I checked into <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=get_value(array($uri=>$resource),  'as:location')?></a>, 
    <?endif?>
  <?elseif(has_type(array($uri=>$resource),  'as:Like')):?>
  The last thing I liked was <a href="<?=get_value(array($uri=>$resource),  'as:object')?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif(has_type(array($uri=>$resource),  'as:Add')):?>
  The last thing I saved was <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a> to <?=get_value(array($uri=>$resource),  'as:target')?>
  <?elseif(has_type(array($uri=>$resource),  'as:Announce')):?>
  The last thing I reposted was <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif(has_type(array($uri=>$resource),  'as:Note')):?>
    The last thing I scribbled was about <em><?=$tags[get_value(array($uri=>$resource),  'as:tag')]["name"]?></em>,
  <?elseif(has_type(array($uri=>$resource),  'as:Article')):?>
  The last article I wrote was <strong><?=get_value(array($uri=>$resource),  'as:name')?></strong>
  <?elseif(has_type(array($uri=>$resource),  'as:Travel')):?>
  The last trip I planned was from <?=get_name($ep, get_value(array($uri=>$resource),  'as:origin'))?> on <?=get_value(array($uri=>$resource),  'as:startTime')?> to <?=get_name($ep, get_value(array($uri=>$resource),  'as:target'))?> at <?=get_value(array($uri=>$resource),  'as:endTime')?> 
  <?elseif(has_type(array($uri=>$resource),  'as:Accept')):?>
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