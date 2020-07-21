<?
$items = get_values($resource, 'as:items', $content->getUri());

if($items == null){
  $items = array();
}
$itemsh = count($items);

if(get_value($resource, 'as:partOf', $content->getUri())){
  $collection_uri = get_value($resource, 'as:partOf', $content->getUri());
  $collection_g = array($collection_uri => $resource[$collection_uri]);
  $itemsh .= " out of ".get_value($collection_g, 'as:totalItems', $collection_uri);
  $collection = array();
  foreach($items as $item){
    $collection[$item] = $resource[$item];
  }
}
?>
<h1><?=get_value($resource, 'as:name')?> (<?=$itemsh?>)</h1>
<ul>
  <?
  if(isset($collection)){
    $sorted = $collection;
    unset($sorted[key($resource)]);
  }else{
    $sorted = get_and_sort($ep, $items);
  }
  $prev_date = false;
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
      $prev_date = new DateTime(get_value($resource, 'as:published'));
    }
  }

  ?>
</ul>
