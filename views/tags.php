<?if($resource->allResources('as:tag')):?>
  <p class="tags"><strong>&#127991;</strong> 
    <?foreach($resource->allResources('as:tag') as $tag):?>
      <?if(isset($tags)):?>
        <a href="<?=$tag->getUri()?>"><?=$tags[$tag->getUri()]["name"]?></a>
      <?endif?>
    <?endforeach?>
  </p>
<?endif?>