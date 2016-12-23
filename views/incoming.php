<?
// Moderation
// /incoming/#public
// /incoming/#private
// can eventually use this for more granular acl, and on a triple level. things can be in more than one graph.
// can check access token and determine which graph to fetch from
?>
<!doctype html>
<html>
  <head>
    <title>Incoming</title>
    <link rel="stylesheet" href="../views/normalize.min.css" />
    <link rel="stylesheet" href="../views/base.css" />
    <link rel="stylesheet" href="../views/core.css" />
    <style type="text/css">
      textarea, input[type=submit] {
        width: 100%;
        border: 1px solid silver;
        padding: 0.4em;
      }
    </style>
  </head>
  <body>
    <article>
      <h1>Notifications</h1>
      <p>This is the API endpoint to which I receive notifications. Notifications which are hosted publicly and externally are listed here. Notifications which aren't go through manual moderation and may or may not be listed publicly. A full list of notifications can be retreived with the appropriate <code>Authorization</code> header.</p>
      <p>Notifications are processed and published according to <a href="https://www.w3.org/TR/ldn/">Linked Data Notifications</a> for receiving.</p>

      <form method="post">
        <p>
          <label for="content">Leave a message</label>
          <textarea id="content" name="content"></textarea>
          <input type="submit" value="Send" />
        </p>
      </form>
      <?if(isset($contains["https://rhiaro.co.uk/incoming/"]["http://www.w3.org/ns/ldp#contains"]) && !empty($contains["https://rhiaro.co.uk/incoming/"]["http://www.w3.org/ns/ldp#contains"])):?>
        <ul>
          <?foreach($contains["https://rhiaro.co.uk/incoming/"]["http://www.w3.org/ns/ldp#contains"] as $item):?>
            <li><a href="$item["value"]"><?=$item["value"]?></a></li>
          <?endforeach?>
        </ul>
      <?else:?>
        <p>Nothing here :(</p>
      <?endif?>

    </article>
  </body>
</html>