<?
if(!isset($locations)){
  $locations = get_locations($ep);
  $locations = $locations->toRdfPhp();
}
?>
<article>
  <h1 class="p-name" property="as:name"><?=get_value($resource, 'as:name')?></h1>
  <div class="p-summary" property="as:summary" style="display: none"><?=get_value($resource, 'as:summary')?></div>
  <div class="e-content" property="as:content"><?=get_value($resource, 'as:content')?></div>
  <?if(!array_key_exists(get_uri($resource), $locations)):?>
    <? include 'map.php'; ?>
  <?endif?>
  <?if(get_values($resource, 'as:tag')):?>

  <?endif?>

</article>