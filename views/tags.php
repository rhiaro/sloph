<?if($resource->all('as:tag')):?>
  <p class="tags"><strong>&#127991;</strong> 
    <?foreach($resource->all('as:tag') as $tag):?>
      <a href="/tag/<?=$tag->getValue()?>"><?=$tag->getValue()?></a>
    <?endforeach?>
  </p>
<?endif?>