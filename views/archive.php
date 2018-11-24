<article class="h-entry" typeof="as:Article" about="">
  <h1>Archive</h1>
  <div class="w1of2"><div class="inner">
    <h2>By date</h2>
    <p>(Counts are for articles and notes only.)</p>
    <ul>
      <?foreach($dates_count as $year => $counts):?>
        <li>
          <a href="/<?=$year?>"><?=$year?></a> (<?=number_format($counts["total"])?>)
          <?if(count($counts) > 1):?>
            <ul>
              <?foreach($counts as $month => $count):?>
                <?if($month != "total"):?>
                  <? $m = new DateTime($year."-".$month."-01"); ?>
                  <li><a href="/<?=$year?>/<?=$month?>"><?=$m->format("F")?></a> (<?=number_format($count)?>)</li>
                <?endif?>
              <?endforeach?>
            </ul>
          <?endif?>
        </li>
      <?endforeach?>
    </ul>
  </div></div>
  <div class="w1of2"><div class="inner">
    <h2>By type</h2>

    <ul>
      <?foreach($types_count as $type):?>
        <li><a href="<?=$type["url"]?>"><?=$type["label"]?></a> (<?=$type["count"]?>)</li>
      <?endforeach?>
    </ul>

    <p>I also used <?=number_format(count($tags))?> different tags, you can <a href="/tags">find posts by tag here</a>.</p>
  </div></div>
</article>