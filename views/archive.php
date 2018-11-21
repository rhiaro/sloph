<?
$types_count = array(
  "as:Article" => array("label" => "articles", "url" => "/articles"), 
  "as:Note" => array("label" => "notes", "url" => "/notes"), 
  "as:Add" => array("label" => "photos and bookmarks", "url" => "/bookmarks"),
  "as:Arrive" => array("label" => "checkins", "url" => "/arrives"),
  "asext:Consume" => array("label" => "food logs", "url" => "/eats"),
  "asext:Acquire" => array("label" => "stuff logs", "url" => "/stuff"),
  "as:Like" => array("label" => "likes", "url" => "/likes"),
  "as:Event" => array("label" => "events", "url" => "/events"),
  "as:Accept" => array("label" => "rsvps", "url" => "/rsvps"),
  "asext:Write" => array("label" => "word count logs", "url" => "/words")
);
foreach($types_count as $type => $data){
  $q = query_count_type($type);
  $res = execute_query($ep, $q);
  $types_count[$type]["count"] = $res["rows"][0]["c"];
}

$now = new DateTime();
$start_year = 2004;
$current_year = $now->format("Y");
// $types = array("as:Article", "as:Note", "as:Add");
$types = array("as:Article", "as:Note");
$dates_count = array();
for($y = $start_year; $y <= $current_year; $y++){
  // Count posts in year
  $year_c = 0;
  $from = new DateTime($y."-01-01");
  $to = new DateTime($y."-12-31");

  foreach($types as $type){
    $q = query_select_count_between_type($from->format(DATE_ATOM), $to->format(DATE_ATOM), $type);
    $res = execute_query($ep, $q);
    $year_c += $res["rows"][0]["c"];
  }

  if($year_c > 0){
    $dates_count[$y]["total"] = $year_c;
  }

  for($m = 1; $m <= 12; $m++){
    // Count posts in month
    $month_c = 0;
    $from = new DateTime($y."-".$m."-01");
    $to = new DateTime($from->format("Y-m-t"));

    foreach($types as $type){
      $q = query_select_count_between_type($from->format(DATE_ATOM), $to->format(DATE_ATOM), $type);
      $res = execute_query($ep, $q);
      $month_c += $res["rows"][0]["c"];
    }

    if($month_c > 0){
      $dates_count[$y][str_pad($m, 2, "0", STR_PAD_LEFT)] = $month_c;
    }
  }
}
krsort($dates_count);
foreach($dates_count as $month=>$counts){
  krsort($dates_count[$month]);
}
?>

<article class="h-entry" typeof="as:Article" about="">
  <h1>Archive</h1>
  <div class="w1of2"><div class="inner">
    <h2>By date</h2>
    <p>(Counts are for articles and notes only.)</p>
    <ul>
      <?foreach($dates_count as $year => $counts):?>
        <li>
          <a href="/<?=$year?>"><?=$year?></a> (<?=$counts["total"]?>)
          <?if(count($counts) > 1):?>
            <ul>
              <?foreach($counts as $month => $count):?>
                <?if($month != "total"):?>
                  <? $m = new DateTime($year."-".$month."-01"); ?>
                  <li><a href="/<?=$year?>/<?=$month?>"><?=$m->format("F")?></a> (<?=$count?>)</li>
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

    <p>I also used <?=count($tags)?> different tags, you can <a href="/tags">find posts by tag here</a>.</p>
  </div></div>
</article>