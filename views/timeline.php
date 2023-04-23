<article class="h-entry" typeof="as:Article" about="">
  <h1>Timeline</h1>
  <div class="w1of1"><div class="inner">
    <div class="timebox">
        <?foreach($markers as $date => $data):?>
          <div class="time" style="height: <?=round($data['diff']/60)?>px">
              <span><?=$data['date']->format("Y-m-d")?></span>
              <!-- <span><?=$data['diff']?></span> -->
          </div>
        <?endforeach?>
    </div>
    <?foreach($timeline as $date => $post):?>
      <div class="bar" style="height: <?=round($post['diff']/60)?>px; background-color: <?=$post['color']?>;">
      </div>
      <div class="info" style="height: <?=round($post['diff']/60)?>px;" title="<?=$post["uri"]?>">
        <span title="<?=$post["uri"]?>">
        <?if(!has_type(array($post["uri"]=>$post), "as:Arrive")):?>
          &lt;
            <?if(has_type(array($post["uri"]=>$post), "as:Travel")):?>
                <?=get_value(array($post["uri"]=>$post), "as:summary")?>
            <?elseif(has_type(array($post["uri"]=>$post), "as:Note") || has_type(array($post["uri"]=>$post), "as:Article") || has_type(array($post["uri"]=>$post), "as:Add")):?>
                <a href="<?=$post["uri"]?>">Words</a>
            <?else:?>
                <?=get_value(array($post["uri"]=>$post), "as:content")?>
            <?endif?>
            <?=get_value(array($post["uri"]=>$post), "as:published")?>
        <?endif?>
        </span>
      </div>
    <?endforeach?>
  </div></div>
</article>