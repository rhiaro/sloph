<?foreach($resource as $uri => $resource):?>
  <? 
    $values = get_values(array($uri=>$resource),  'rdf:type');
    $types = array();
    foreach($values as $type){
      $type = EasyRdf_Namespace::shorten($type);
      if($type != "as:Activity"){
        $types[] = $type;
      }
    }
  ?>
  <p><span><?=get_icon_from_type($types)?></span> 
  <?if(in_array('asext:Consume', $types)):?>
    The last thing I ate was <?=get_value(array($uri=>$resource),  'as:content')?>, 
  <?elseif(in_array('asext:Acquire', $types)):?>
    The last thing I acquired was <?=get_value(array($uri=>$resource),  'as:content')?>, for <?=get_value(array($uri=>$resource),  'asext:cost')?>, 
  <?elseif(in_array('as:Arrive', $types)):?>
    <?if(get_value($locations, 'blog:presentLabel', get_value(array($uri=>$resource),  'as:location'))):?>
    <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=get_value($locations, 'blog:presentLabel', get_value(array($uri=>$resource),  'as:location'))?></a> since 
    <?else:?>
    I checked into <a href="<?=get_value(array($uri=>$resource),  'as:location')?>"><?=get_value(array($uri=>$resource),  'as:location')?></a>, 
    <?endif?>
  <?elseif(in_array('as:Like', $types)):?>
  The last thing I liked was <a href="<?=get_value(array($uri=>$resource),  'as:object')?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif(in_array('as:Add', $types)):?>
    
    The last collection I added to was <a href="<?=get_value(array($uri=>$resource),  'as:target')?>"><?=get_value(array($uri=>$resource),  'as:target')?></a>
    <? $objects = get_values(array($uri=>$resource), 'as:object'); ?>
    <?if(count($objects) > 1):?>
      (<?=count($objects)?> items)
      <? $max = 4;
      if(count($objects) < 5){ $max = count($objects); }
      ?>
      <div class="w1of1 clearfix">
        <?for($i=0;$i<$max;$i++):?>
          <a href="<?=$objects[$i]?>"><img class="w1of5" src="<?=$objects[$i]?>" alt="<?=$objects[$i]?>" /></a>
        <?endfor?>
          <a class="w1of5" href="<?=$uri?>" title="more" style="font-size: 3em; line-height: 2; text-decoration: none">&nbsp;&nbsp; ... </a>
      </div>
    <?else:?>
     <span style="font-size: 1.6em;">&cularrp;</span> <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
    <?endif?>

  <?elseif(in_array('as:Announce', $types)):?>
  The last thing I reposted was <a href="<?=get_uri($resource) ?>"><?=get_value(array($uri=>$resource),  'as:name') ? get_value(array($uri=>$resource),  'as:name') : get_value(array($uri=>$resource),  'as:object')?></a>
  <?elseif(in_array('as:Note', $types)):?>
    The last thing I scribbled was about <a href="<?=get_value(array($uri=>$resource),  'as:tag')?>"><?=$tags[get_value(array($uri=>$resource),  'as:tag')]["name"]?></a>,
  <?elseif(in_array('as:Article', $types)):?>
  The last article I wrote was <strong><?=get_value(array($uri=>$resource),  'as:name')?></strong>
  <?elseif(in_array('as:Travel', $types)):?>
  The last trip I planned was from <?=get_name($ep, get_value(array($uri=>$resource),  'as:origin'))?> on <?=get_value(array($uri=>$resource),  'as:startTime')?> to <?=get_name($ep, get_value(array($uri=>$resource),  'as:target'))?> at <?=get_value(array($uri=>$resource),  'as:endTime')?> 
  <?elseif(in_array('as:Accept', $types)):?>
    <?
      $event_uri = get_value(array($uri=>$resource),  'as:object');
      if(!$event_uri){ $event_uri = get_value(array($uri=>$resource),  'as:inReplyTo'); }
      if($event_uri){
        // See if event is in the store
        $event = get($ep, $event_uri);
        if($event['content']){
          $event = $event['content']->toRdfPhp();
          $start = new DateTime(get_value($event, 'as:startTime'));
          $start = $start->format("d F");
          $end = new DateTime(get_value($event, 'as:endTime'));
          $end = $end->format("d F");
          $ename = get_value($event, 'as:name') ? get_value($event, 'as:name') : $event_uri;
          $elocation = get_value($event, 'as:location');
        }else{
          $start = $end = "unknown time";
          $ename = $event_uri;
          $elocation = "https://rhiaro.co.uk/location/event";
        }
        if($elocation){
          $elocation_str = "at ".get_name($ep, $elocation);
        }else{
          $elocation_str = "";
        }
      }
      
    ?>
    The last event I RSVP'd to was <strong><?=$ename?></strong>, taking place from <?=$start?> to <?=$end?> <?=$elocation_str?>,  
  <?endif?>

  <?if(get_value(array($uri=>$resource),  'as:published')):?>
    <time><a href="<?=$uri?>"><?=time_ago(get_value(array($uri=>$resource),  'as:published'))?></a></time></p>
  <?endif?>
<?endforeach?>