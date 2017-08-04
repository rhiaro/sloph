<?
// Moderation
// /incoming/moderation
// /incoming/public
// /incoming/private
// can eventually use this for more granular acl, and on a triple level. things can be in more than one graph.
// can check access token and determine which graph to fetch from
$listing = get_values($contains, "ldp:contains");
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
        width: 99%;
        border: 1px solid silver;
        padding: 0.4em;
      }
      .robot {
        text-align: center;
        position: relative;
        z-index: 1;
        max-width: 440px; margin-left: auto; margin-right: auto;
      }
      .robot img { position: relative; margin-bottom: -2em; z-index: -1; }
      .robot input {
        z-index: 1;
        border: 1px solid silver;
        padding: 0.4em;
        width: 99%; max-width: 380px;
      }
    </style>
  </head>
  <body>
    <article>
      <h1>Notifications</h1>
      <p>This is the API endpoint to which I receive notifications. Notifications which are hosted publicly and externally are listed here. Notifications which aren't go through manual moderation and may or may not be listed publicly. A full list of notifications can be retreived with the appropriate <code>Authorization</code> header.</p>
      <p>Notifications are processed and published according to <a href="https://www.w3.org/TR/ldn/">Linked Data Notifications</a> for receiving.</p>

      <?if(isset($sent) && $sent):?>
        <p class="win"><em>Thanks! Your message has been queued for moderation.</em></p>
      <?endif?>

      <?if(isset($robot) && $robot):?>
        <p class="fail"><em>...but you are so magnetic.. you pick up all the pins.</em></p>
      <?endif?>

      <form method="post">
        <p>
          <label for="content">Leave a message</label>
          <textarea id="content" name="content"></textarea>
        </p>
        <p class="robot">
          <label for="notawhat"><a href="https://www.youtube.com/watch?v=S_oMD6-6q5Y" target="_blank"><img src="/views/notarobot.jpg" alt="Better to be hated than loved, loved, loved for what you're not (Marina Diamandis)" /></a></label>
          <input type="text" id="notawhat" name="notawhat" placeholder="I am not a .. " />
        </p>
        <p>
          <input type="submit" value="Send" />
        </p>
      </form>
      <p><?=count($listing)?> notifications in my moderation queue.</p>

    </article>
  </body>
</html>