<?if(get_values($resource, 'as:tag')):?>
  <p class="tags"><strong>&#127991;</strong> 
    <?foreach(get_values($resource, 'as:tag') as $tag):?>
      <?if(isset($tags) && isset($tags[$tag])):?>
        <a href="<?=$tag?>"><?=$tags[$tag]["name"]?></a>
      <?endif?>
    <?endforeach?>
  </p>
<?endif?>