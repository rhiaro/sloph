<article class="h-entry" typeof="as:Article" about="">
  <h1>Articles index</h1>
  <form id="filter">
    <label>Filter</label>
    <select name="yfilter" id="yfilter">
      <option value="0"<?=!isset($yinclude) ? " selected" : ""?>>all years</option>
      <?foreach($y_filter_opts as $y):?>
        <option value="<?=$y?>"<?=(isset($yinclude) && $yinclude==$y) ? " selected": ""?>><?=$y?></option>
      <?endforeach?>
    </select>
    <select name="mfilter" id="mfilter">
      <option value="0"<?=!isset($minclude) ? " selected" : ""?>>all months</option>
      <?if(isset($yinclude)):?>
        <?foreach($m_filter_opts[$yinclude] as $m):?>
          <option value="<?=$m?>"<?=(isset($minclude) && $minclude==$m) ? " selected" : ""?>><?=$monthname[$m]?></option>
        <?endforeach?>
      <?else:?>
        <?foreach($monthname as $m => $name):?>
          <option value="<?=$m?>"<?=(isset($minclude) && $minclude==$m) ? " selected" : ""?>><?=$name?></option>
        <?endforeach?>
      <?endif?>
    </select>
    <input type="submit" value="filter" name="filter" />
  </form>
  <?foreach($sorted_by_month as $year => $months):?>
    <div id="<?=$year?>">
    <h2><?=$year?></h2>
      <?foreach($months as $month => $articles):?>
        <h3><?=$monthname[$month]?></h3>
        <ul id="<?=$month?>">
          <?foreach($articles as $uri => $data):?>
            <li><a href="<?=$uri?>"><?=article_name($uri,$data)?></a></li>
          <?endforeach?>
        </ul>
      <?endforeach?>
    </div>
  <?endforeach?>

</article>
<script src="views/listing_articles.js"></script>