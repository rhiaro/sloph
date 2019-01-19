<?
$items = get_values($resource, "as:items");
?>
<article class="h-entry" typeof="as:Article" about="">
  <h1>Timeline</h1>
  <div class="w1of2"><div class="inner">
    <?foreach($items as $item):?>
      <p><?=get_value(array($item => $resource[$item]), "as:published")?></p>
    <?endforeach?>
  </div></div>
</article>