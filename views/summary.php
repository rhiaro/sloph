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
      <p>

    </article>
  </body>
</html>