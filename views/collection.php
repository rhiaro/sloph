<? $items = get_values($resource, 'as:items'); ?>
<h1><?=get_value($resource, 'as:name')?> (<?=count($items)?>)</h1>
<ul>
  <?
  if(!isset($collection)){
    $sorted = get_and_sort($ep, $items);
  }else{
    $sorted = $collection;
    unset($sorted[key($resource)]);
  }
  foreach($sorted as $uri => $resource){
    $resource = array($uri => $resource);

    if(has_type($resource, "as:Arrive")){
      echo "<article>";
      // Collections of checkins need more delicate handling
      // TODO: make this nice
      //       Probably add something to the actual checkin template to see if it's in a loop
      $date = new DateTime(get_value($resource, 'as:published'));
      if(array_key_exists(get_value($resource, 'as:location'), $locations)){
        $location = array(get_value($resource, 'as:location') => $locations[get_value($resource, 'as:location')]);
      }else{
        $location = get_value($resource, 'as:location');
      }
      if(get_value($location, 'blog:pastLabel')){
      ?>
        <p><?=get_value($location, 'blog:pastLabel')?> at <?=$date->format("g:ia (e) \o\\n l \\t\h\\e jS \o\\f F")?></p>
      <?
      }else{
        include 'views/map.php';
      }
      echo "</article>";
    }else{
      include 'views/'.view_router($resource).'.php';
    }
  }

  ?>
</ul>
