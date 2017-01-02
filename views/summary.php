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
      <h2>Writing</h2>
      <p>I wrote <?=number_format($writing['total'])?> things on my site. <?=number_format($writing['notes'])?> were short notes, <?=number_format($writing['articles'])?> were longer articles, and they comprise approximately <?=number_format($writing['words'])?> words in total. That's a mean of <?=number_format($writing['dailywords'], 3)?> words and <?=number_format($writing['dailynotes'], 3)?> posts per day.</p>
      <p>I wrote about <?=$writing['tags']?> different topics, with the most common being <?=$writing['toptags']?>.</p>

      <h2>Travel</h2>
      <p>I checked in <?=number_format($checkins['total'])?> times. I spent the most time <a href="<?=$checkins['top'][0]['location']?>"><?=$checkins['top'][0]['label']?></a>, which was <?=$checkins['top'][0]['duration']?>, followed by <?=$checkins['top'][1]['duration']?> <a href="<?=$checkins['top'][1]['location']?>"><?=$checkins['top'][1]['label']?></a>. I also spent
      <?for($i=2;$i<count($checkins['top'])-1;$i++):?>
        <?=$checkins['top'][$i]['duration']?> <a href="<?=$checkins['top'][$i]['location']?>"><?=$checkins['top'][$i]['label']?></a>; 
      <?endfor?>
      and was <a href="<?=$checkins['top'][count($checkins['top'])-1]['location']?>"><?=$checkins['top'][count($checkins['top'])-1]['label']?></a> for <?=$checkins['top'][count($checkins['top'])-1]['duration']?>.
      </p>
      <p>I planned x journeys, to y different places. I travelled primarily by x, followed by y and z. Some places I visited are a, b, c, d, e and f.</p>

      <h2>Acquisitions</h2>

      <p>I purchased or otherwise acquired something on x occasions, spending a total of &pound;y. I used z different currencies (USD, GBP, JPY, EUR, MYR, ...). This is an average expenditure of x per week, or z per month. I mostly bought [food] (%) of which % was takeaway and % was in restaurants. I spent x on groceries and y on household. I also spent z on travel or transit. On x occasions I got something for free. The most expensive thing I bought was x and the cheapest thing (which wasn't free) was y. I spent on average z per time. Other categories of expenditure are: ... .</p>

      <p>% of my acquire posts have photos attached. You can see them all at <a href="/stuff">/stuff</a>.</p>

      <h2>Consumption</h2>
      <p>I logged x meals or snacks, an average of y per day. The thing I consumed most was [probably coffee] (x instances), followed by [bread] and [vegetables].</p>
      <p>Other things I ate were: ... . You can see them all at <a href="/eats">/eats</a>.</p>

      <h2>Socialling</h2>

      <p>I <a href="/likes">liked</a> x links, y% of which were from Twitter. I <a href="/bookmarks">bookmarked</a> x links, and posted y images to collections over z occasions. I <a href="/reposts">reposted</a> something x times. y of my posts were in reply to something else.</p>

    </article>
  </body>
</html>