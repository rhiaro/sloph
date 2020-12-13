<!doctype html>
<html>
  <head>
    <title>Summary</title>
    <link rel="stylesheet" href="../views/normalize.min.css" />
    <link rel="stylesheet" href="../views/base.css" />
    <link rel="stylesheet" href="../views/core.css" />
  </head>
  <body>
    <article>
      <h1>from <?=$from->format("d M y")?> to <?=$to == $now ? "now" : $to->format("d M y")?></h1>
      <p>I posted to my site <?=number_format($total)?> times.</p>

      <?if($writing['reviews'] > 0):?>
        <p>Reviews:</p>
        <ul>
          <?foreach($writing['reviewposts'] as $reviewuri => $reviewpost):?>
            <li><a href="<?=$reviewuri?>"><?=$reviewpost['name']?></a></li>
          <?endforeach?>
        </ul>
      <?endif?>

      <h2 id="writing">Writing</h2>
      <p>I wrote <?=number_format($writing['total'])?> things. On my site I posted <?=number_format($writing['notes'])?> short notes or commentary with photos, and <?=number_format($writing['articles'])?> longer articles. I also logged non-blogpost writing <?=number_format($writing['wrote'])?> times. These all comprise approximately <?=number_format($writing['words'])?> words in total (<?=number_format($writing['wrotetotal'])?> off-site). That's a mean of <?=number_format($writing['dailywords'], 2)?> words and <?=number_format($writing['dailynotes'], 2)?> posts per day.</p>
      <p>I wrote about <?=$writing['tags']?> different topics, with the most common being <?=$writing['toptags']?>.</p>

      <h2 id="travel">Travel</h2>
      <p>I checked in <?=number_format($checkins['total'])?> times. I spent the most time <a href="<?=$checkins['top'][0]['location']?>"><?=$checkins['top'][0]['label']?></a>, which was <?=$checkins['top'][0]['duration']?>, followed by <?=$checkins['top'][1]['duration']?> <a href="<?=$checkins['top'][1]['location']?>"><?=$checkins['top'][1]['label']?></a>. I also spent
      <?for($i=2;$i<count($checkins['top'])-1;$i++):?>
        <?=$checkins['top'][$i]['duration']?> <a href="<?=$checkins['top'][$i]['location']?>"><?=$checkins['top'][$i]['label']?></a>;
      <?endfor?>
      and was <a href="<?=$checkins['top'][count($checkins['top'])-1]['location']?>"><?=$checkins['top'][count($checkins['top'])-1]['label']?></a> for <?=$checkins['top'][count($checkins['top'])-1]['duration']?>.
      </p>

      <p>I spent &euro;<?=number_format($acquires['transitEur'], 2)?> on <a href="https://rhiaro.co.uk/tags/transit">transit</a>/<a href="https://rhiaro.co.uk/tags/transport">transport</a>, over <?=$acquires['transitNum']?> journeys<?=$acquires['transitMeans']?>.</p>

      <p>I planned x journeys, to y different places. I travelled primarily by x, followed by y and z. Some places I visited are a, b, c, d, e and f.</p>

      <h2 id="shelter">Shelter</h2>

      <?if(!empty($acquires['accom'])):?>
        <p>I lay my head in <?=count($acquires['accom'])?> different places:</p>
        <ul>
          <?foreach($acquires['accom'] as $accom):?>
            <li><?=!empty($accom['startTime']) ? $accom['startTime']->format("jS M Y").' ' : ''?><?=!empty($accom['endTime']) ? 'to '.$accom['endTime']->format("jS M Y").': ' : ''?><a href="<?=$accom['uri']?>"><?=$accom['content']?></a> (<?=$accom['cost']?>)</li>
          <?endforeach?>
        </ul>
      <?else:?>
        <p>No logs for where I stayed yet during this time.</p>
      <?endif?>
      <?if(!empty($acquires['accomother'])):?>
        <p>And also used:</p>
        <ul>
          <?foreach($acquires['accomother'] as $accomother):?>
            <li><a href="<?=$accomother['uri']?>"><?=$accomother['content']?></a> (<?=$accomother['cost']?>)</li>
          <?endforeach?>
        </ul>
      <?endif?>

      <p>I spent &euro;<?=number_format($acquires['accomEur'], 2)?> in total, averaging &euro;<?=number_format($acquires['accomMean'], 2)?> per night<?=is_numeric($acquires['accomMonth']) ? ", and &euro;".number_format($acquires['accomMonth'], 2)." per month" : ""?>.</p>

      <h2 id="consumption">Consumption</h2>
      <p>I logged <?=$consumes['total']?> meals or snacks, an average of <?=number_format($consumes['day'], 1)?> per day. The thing I consumed most was <?=$consumes['top']?>, followed by <?=$consumes['toptags']?>. I consumed <?=$consumes['top']?> on average <?=number_format($consumes['topday'], 1)?> times per day.</p>

      <p>I spent &euro;<?=$acquires['food']['groceriesEur']?> on groceries, buying them <?=$acquires['food']['groceries']?> times. I bought food that was ready to eat on <?=$acquires['food']['total']?> occasions, spending &euro;<?=$acquires['food']['foodEur']?>; <?=$acquires['food']['restaurant']?>% of the time this was in restaurants and <?=$acquires['food']['takeaway']?>% to take away.</p>

      <p>One random thing I ate was <?=$consumes['random']?>. You can see everything at <a href="/eats">/eats</a>.</p>

      <h2 id="acquisitions">Acquisitions</h2>
      <p>I purchased or otherwise acquired something on <?=$acquires['total']?> occasions, spending a total of approximately &euro;<?=number_format($acquires['totaleur'], 2)?>. I used <?=count($acquires['currencies'])?> different currencies (<?=implode(", ", $acquires['currencies'])?>). This is an average expenditure of &euro;<?=$acquires['day']?> per day, &euro;<?=$acquires['week']?> per week, or &euro;<?=$acquires['month']?> per month. </p>

      <?if(!empty($acquires['toptags'])):?>
        <p>Some things I acquired the most often were <?=$acquires['toptags']?>. </p>
      <?endif?>

      <p>On <?=$acquires['free']?> occasions I got something for free. I expensed &euro;<?=$acquires['expensed']?> of stuff for work. The most expensive thing I bought was <a href="<?=$acquires['dearest']['uri']?>"><?=$acquires['dearest']['content']?></a> (&euro;<?=number_format($acquires['dearest']['amountEur'], 2)?>) and the cheapest thing (which wasn't free) was <a href="<?=$acquires['dearest']['uri']?>"><?=$acquires['cheapest']['content']?></a> (&euro;<?=number_format($acquires['cheapest']['amountEur'], 2)?>). I spent on average &euro;<?=$acquires['meaneur']?> per time.
      <?if(!empty($acquires['othertags'])):?>
        Three other random categories of expenditure are: <?=$acquires['othertags']?>.
      <?endif?>
      </p>

      <p><?=$acquires['photosp']?>% of my acquire posts have photos attached.
      <?if($acquires['photosp'] > 0):?>
        You can see them all at <a href="/stuff">/stuff</a>. Here's a random one (this was <?=$acquires['photocost']?> and I acquired it on <?=$acquires['photodate']->format("jS F Y \a\\t h:ia")?>):</p>
        <p class="w1of1" style="text-align:center;"><img src=<?=$acquires['photo']?> alt="<?=$acquires['photocont']?>" title="<?=$acquires['photocont']?>" />
      <?endif?>
      </p>

      <h2 id="socialling">Socialling</h2>

      <p>I <a href="/likes">liked</a> x links, y% of which were from Twitter. I <a href="/bookmarks">bookmarked</a> x links, and posted y images to collections over z occasions. I <a href="/reposts">reposted</a> something x times. y of my posts were in reply to something else.</p>

    </article>
  </body>
</html>