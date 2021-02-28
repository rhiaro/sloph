<header>
  <div class="rhiaro">
    <img src="https://rhiaro.co.uk/stash/dp.png" id="me" alt="closeup picture of me with a parrot in my face" />
    <img src="https://rhiaro.co.uk/stash/dp1.png" id="me" alt="closeup picture of me with a parrot in my hair" />
  </div>
  <div class="projects">
    <h1><span>rhiaro</span></h1>
    <p><span>Timezone: <strong><?=current_timezone($ep);?></strong> (<?=$now->format("jS M H:i")?>)</span></p>
    <p><span>Currently <strong><a href="<?=$checkin_summary["location_uri"]?>"><?=$checkin_summary["location"]?></a></strong> (for <a href="/where"><?=$checkin_summary["for"]?></a>)</span></p>
    <p><span style="opacity: 0.8">You may know me from..</span></p>
    <?foreach($project_icons as $group):?>
      <div>
        <?foreach($group as $project):?>
          <a href="<?=$project["uri"]?>" class="project-box" title="<?=$project["name"]?>" style="background-color: <?=$project["color"]?>"><img src="/<?=$project["icon"]?>" alt="<?=$project["name"]?>" title="<?=$project["name"]?>" /></a>
        <?endforeach?>
      </div>
    <?endforeach?>
  </div>
  <div class="stats">
    <p><a href="/eats">Last ate</a> <?=time_ago($consume_stats["published"])?> (<a href="<?=$consume_stats["uri"]?>"><?=$consume_stats["content"]?></a>)</p>
    <div class="stat-box"><div style="width: <?=$consume_stats["width"]?>;" class="<?=$consume_stats["color"]?>"></div></div>
    <p>Last exercised <?=time_ago($exercise_stats["published"])?></p>
    <div class="stat-box"><div style="width: <?=$exercise_stats["width"]?>;" class="<?=$exercise_stats["color"]?>"></div></div>
    <?if(isset($budget_stats["uri"])):?>
      <p>Monthly budget <?=$budget_stats["perc"]?>% used (<a href="/stuff">last spent</a> <?=$budget_stats["cost"]?> on <a href="<?=$budget_stats["uri"]?>"><?=$budget_stats["content"]?></a>)</p>
    <?else:?>
      <p>Budget: nothing spent so far this month</p>
    <?endif?>
    <div class="stat-box"><div style="width: <?=$budget_stats["width"]?>;" class="<?=$budget_stats["color"]?>"></div></div>
    <p>Words written this month (<?=$words_stats["value"]?> of posts and fiction)</p>
    <div class="stat-box"><div style="width: <?=$words_stats["width"]?>;" class="<?=$words_stats["color"]?>"></div></div>
  </div>
</header>