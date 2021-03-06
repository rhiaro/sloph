<!doctype html>
<html>
  <head>
    <title>National Novel Writing Month</title>
    <link rel="stylesheet" href="../views/normalize.min.css" />
    <link rel="stylesheet" href="../views/base.css" />
    <link rel="stylesheet" href="../views/core.css" />
    <link rel="stylesheet" href="../views/nano.css" />
  </head>
  <body>
    <article>
      <h1>National Novel Writing Month</h1>
      <p>I have been taking part in <a href="https://nanowrimo.org">nanowrimo</a> since 2008, minus 2014 to 2016 when I wrote a PhD thesis instead. The stated goal is to write 50,000 words of original prose in 30 days. Many people have their own personal goals of course, and for me writing more than nothing is a win. Still, I use the 50k as a motivator, and aim for the minimum 1667 words per day in order to meet it.</p>

      <p>The wordcounts listed here are what I wrote during the nanowrimo month, not necessarily the total or current wordcount for the whole stories.</p>

      <p><a href="https://rhiaro.co.uk/tags/nanowrimo">See all blog posts tagged #nanowrimo</a>.</p>

      <section class="nanowrimo w1of1">
        <div class="w1of5"><div class="inner"><img src="https://d1lj9l30x2igqs.cloudfront.net/nano-2013/files/2018/08/NaNo-Shield-Logo-Web.png" alt="NaNoWriMo logo" title="Image courtesy of National Novel Writing Month." /></div></div>
        <div class="w4of5"><div class="inner">
          <h2>Is it NaNoWriMo yet?</h2>
          <p><strong><?=$isit["big"]?></strong></p>
          <p><?=$isit["small"]?></p>
        </div></div>
      </section>

      <?foreach($years as $year=>$novel):?>
        <section id="<?=$year?>">
          <h2><?=$year?>: <?=$novel['name']?></h2>
          <p><strong>Wordcount:</strong> <?=$novel['wordcount']?></p>
          <?=$novel['content']?>
        </section>
      <?endforeach?>

    </article>
  </body>
</html>