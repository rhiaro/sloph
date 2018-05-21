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
      
      <h2 id="writing">Writing</h2>
      <p>I wrote <?=number_format($writing['total'])?> things on my site. <?=number_format($writing['notes'])?> were short notes, <?=number_format($writing['articles'])?> were longer articles, and they comprise approximately <?=number_format($writing['words'])?> words in total. That's a mean of <?=number_format($writing['dailywords'], 2)?> words and <?=number_format($writing['dailynotes'], 2)?> posts per day.</p>
      <p>I wrote about <?=$writing['tags']?> different topics, with the most common being <?=$writing['toptags']?>.</p>

      <h2 id="travel">Travel</h2>
      <p>I checked in <?=number_format($checkins['total'])?> times. I spent the most time <a href="<?=$checkins['top'][0]['location']?>"><?=$checkins['top'][0]['label']?></a>, which was <?=$checkins['top'][0]['duration']?>, followed by <?=$checkins['top'][1]['duration']?> <a href="<?=$checkins['top'][1]['location']?>"><?=$checkins['top'][1]['label']?></a>. I also spent
      <?for($i=2;$i<count($checkins['top'])-1;$i++):?>
        <?=$checkins['top'][$i]['duration']?> <a href="<?=$checkins['top'][$i]['location']?>"><?=$checkins['top'][$i]['label']?></a>; 
      <?endfor?>
      and was <a href="<?=$checkins['top'][count($checkins['top'])-1]['location']?>"><?=$checkins['top'][count($checkins['top'])-1]['label']?></a> for <?=$checkins['top'][count($checkins['top'])-1]['duration']?>.
      </p>

      <p>I spent &euro;<?=$acquires['transitEur']?> on <a href="https://rhiaro.co.uk/tags/transit">transit</a>/<a href="https://rhiaro.co.uk/tags/transport">transport</a>, over <?=$acquires['transitNum']?> journeys<?=$acquires['transitMeans']?>.</p>

      <p>I planned x journeys, to y different places. I travelled primarily by x, followed by y and z. Some places I visited are a, b, c, d, e and f.</p>

      <h2 id="shelter">Shelter</h2>

      <p>I lay my head in (approximately; logs may be incomplete) <?=count($acquires['accom'])?> different places:</p>
      <ul>
        <?foreach($acquires['accom'] as $accom):?>
          <li><a href="<?=$accom['uri']?>"><?=$accom['content']?></a> (<?=$accom['cost']?>)</li>
        <?endforeach?>
      </ul>

      <p>I spent &euro;<?=$acquires['accomEur']?> in total, averaging &euro;<?=$acquires['accomMean']?> per night.</p>

      <h2 id="consumption">Consumption</h2>
      <p>I logged <?=$consumes['total']?> meals or snacks, an average of <?=number_format($consumes['day'], 1)?> per day. The thing I consumed most was <?=$consumes['top']?>, followed by <?=$consumes['toptags']?>. I consumed <?=$consumes['top']?> on average <?=number_format($consumes['topday'], 1)?> times per day.</p>

      <p>I spent &euro;<?=$acquires['food']['groceriesEur']?> on groceries, buying them <?=$acquires['food']['groceries']?> times. I bought food that was ready to eat on <?=$acquires['food']['total']?> occasions, spending &euro;<?=$acquires['food']['foodEur']?>; <?=$acquires['food']['restaurant']?>% of the time this was in restaurants and <?=$acquires['food']['takeaway']?>% to take away.</p>

      <p>One random thing I ate was <?=$consumes['random']?>. You can see everything at <a href="/eats">/eats</a>.</p>

      <h2 id="acquisitions">Acquisitions</h2>
      <p>I purchased or otherwise acquired something on <?=$acquires['total']?> occasions, spending a total of approximately &euro;<?=$acquires['totaleur']?>. I used <?=count($acquires['currencies'])?> different currencies (<?=implode(", ", $acquires['currencies'])?>). This is an average expenditure of &euro;<?=$acquires['day']?> per day, &euro;<?=$acquires['week']?> per week, or &euro;<?=$acquires['month']?> per month. </p>

      <p>Some things I acquired the most often were <?=$acquires['toptags']?>. </p>

      <p>On <?=$acquires['free']?> occasions I got something for free. The most expensive thing I bought was <a href="<?=$acquires['dearest']['uri']?>"><?=$acquires['dearest']['content']?></a> (&euro;<?=$acquires['dearest']['amountEur']?>) and the cheapest thing (which wasn't free) was <a href="<?=$acquires['dearest']['uri']?>"><?=$acquires['cheapest']['content']?></a> (&euro;<?=$acquires['cheapest']['amountEur']?>). I spent on average &euro;<?=$acquires['meaneur']?> per time. Three other random categories of expenditure are: <?=$acquires['othertags']?>.</p>

      <p><?=$acquires['photosp']?>% of my acquire posts have photos attached. You can see them all at <a href="/stuff">/stuff</a>. Here's a random one (this was <?=$acquires['photocost']?> and I acquired it on <?=$acquires['photodate']->format("jS F Y \a\\t h:ia")?>):</p>
      <p class="w1of1" style="text-align:center;"><img src=<?=$acquires['photo']?> alt="<?=$acquires['photocont']?>" title="<?=$acquires['photocont']?>" /></p>

      <h2 id="socialling">Socialling</h2>

      <p>I <a href="/likes">liked</a> x links, y% of which were from Twitter. I <a href="/bookmarks">bookmarked</a> x links, and posted y images to collections over z occasions. I <a href="/reposts">reposted</a> something x times. y of my posts were in reply to something else.</p>

    </article>
  </body>
</html>