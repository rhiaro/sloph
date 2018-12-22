<?
$date = new DateTime(get_value($resource, 'as:published'));
$objects = get_values($resource, 'as:object');
$count = count($objects);
$scripts = array("/views/images.js");
if(!isset($in_feed)){
    $in_feed = false;
}
$images = get_values($resource, 'as:image');
if(count($images) < 1){
    $images = array_slice($objects, 0, 4);
}
if($in_feed){
    $objects = $images;
}
?>
<article>
  <h2><span>&#128449;</span>Added <?=$count?> photos to album <a href="<?=get_value($resource, 'as:target')?>"><?=get_name($ep, get_value($resource, 'as:target'))?></a>.</h2>
  
  <?if(count($objects) > 8):?>
      <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
  <?endif?>
  
  <div class="w1of1 clearfix">
    <?foreach($objects as $item):?>
      <img class="w1of4" src="<?=$_IMG?>200/0/<?=$item?>" alt="" />
    <?endforeach?>
    <?if($count > count($objects)):?>
        <p style="text-align: right"><em><a href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>">more</a> &gt;</em></p>
    <?endif?>
  </div>

  <?if(count($objects) <= 8):?>
      <?=get_value($resource, 'as:content') ? "<p>".get_value($resource, 'as:content')."</p>" : "" ?>
  <?endif?>

  <? include('tags.php'); ?>
  <?if(get_value($resource, 'as:published')):?>
    <? $date = new DateTime(get_value($resource, 'as:published')); ?>
    <p><time class="dt-published"><a property="as:published" class="u-url" href="<?=str_replace("https://rhiaro.co.uk", "", get_uri($resource))?>"><?=$date->format("D jS \o\\f F \a\\t g:ia (e)")?></a></time></p>
  <?endif?>
  
</article>
<script>
  var proxyUrl ='<?=$_IMG?>';
</script>