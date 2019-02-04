<article class="h-entry" typeof="as:Article" about="">
  <h1>Timeline</h1>
  <div class="w1of2"><div class="inner">
    <?foreach($timeline as $date => $post):?>
      <div class="bar" style="height: <?=round($post['diff']/60)?>px;"></div>
    <?endforeach?>
  </div></div>
</article>