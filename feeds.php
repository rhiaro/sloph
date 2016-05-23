<?
session_start();
require_once('vendor/init.php');

function feed_consume($ep){

  $vals = array("rdf:type" => "blog:Consumption", "as:published" => "?date");
  $q = query_select_s_where($vals, 64, "date");

  $r = execute_query($ep, $q);
  if($r){
    $posts = construct_uris($ep, select_to_list($r, array("uri")));
  }

  return $posts;
}

function feed_acquire($ep, $tag=null){

  $vals = array("rdf:type" => "blog:Acquisition", "as:published" => "?date");
  if($tag){
    $vals["as:tag"] = "\"".$tag."\"";
  }
  // TODO date
  
  $q = query_select_s_where($vals, 64, "date");

  $r = execute_query($ep, $q);
  if($r){
    $posts = construct_uris($ep, select_to_list($r, array("uri")));
  }

  return $posts;
}

function feed_arrive($ep){
  
  $vals = array("as:location" => "?location", "as:published" => "?date");
  $q = query_select_s_where($vals, 10, "date");

  $r = execute_query($ep, $q);
  if($r){
    $posts = construct_uris($ep, select_to_list($r, array("uri")));
  }

  return $posts;
}

if(isset($_GET['feed'])){
  $feed = $_GET['feed'];
  switch($feed){
    case "consume":
      $posts = feed_consume($ep);
      break;

    case "stuff":
      if(isset($_GET['tag'])){
        $posts = feed_acquire($ep, $_GET['tag']);
      }else{
        $posts = feed_acquire($ep);
      }
      break;

    case "checkin":
      $posts = feed_arrive($ep);
      break;
  }
}

?>
<!doctype html>
<html>
  <head>
    <title>Sloph</title>
    <style>
      body { margin: 0; padding: 0; }
      ul { list-style: none; padding: 0; margin: 0; }
      li { 
        float: left; width: 24em; height: 24em; overflow: hidden; 
        border-radius: 50%;
        border: 16px solid #92C492;
        background-position: center; background-color: #c1dec0;
        background-size: auto 24em; background-repeat: no-repeat;
      }
      li article {
        padding: 4em; text-align: center;
      }
      .tag, .tag a, .tag a:visited {
        background-color: #92C492; color: white;
        border-radius: 0.4em; padding: 0.2em;
        margin: 0 0.1em; line-height: 2;
        white-space: nowrap;
        font-weight: bold; font-size: 0.8em;
        font-family: Arial, sans-serif;
        text-decoration: inherit;
      }
      .tag:hover {
        background-color: #c1dec0;
      }
      .cost { font-size: 2.4em; }
      .date { font-size: 1em; }
      .cost, .date {
        color: white; padding: 0; margin: 0;
        font-weight: bold; 
        text-shadow:
         -1px -1px 0 #000,  
          1px -1px 0 #000,
          -1px 1px 0 #000,
           1px 1px 0 #000;
        font-family: Arial, sans-serif;
      }
      .desc {
        background-color: #c1dec0;
        opacity: 0.8;
        padding: 0.2em;
      }
      li p, li span {
        display: none;
      }
      li:hover p {
        display: block;
      }
      li:hover span {
        display: inline;
      }
    </style>
  </head>
  <body>
    <h1>Sloph</h1>
    <ul>
      <?foreach($posts as $uri => $post):?>
        <?
        $date = new DateTime($post[$_PREF['as']."published"][0]['value']);
        ?>
        <li<?=isset($post[$_PREF['as']."image"]) ? " style=\"background-image: url('".$post[$_PREF['as']."image"][0]['value']."');\"" : ""?>>
          <article>
            <p class="date"><?=$date->format('D jS M H:i')?></p>
            <p><span class="desc"><?=$post[$_PREF['as']."summary"][0]['value']?></span></p>
            <p class="cost"><?=$post[$_PREF['blog']."cost"][0]['value']?></p>
            <p>
              <?foreach($post[$_PREF['as']."tag"] as $tag):?>
                <span class="tag"><a href="?feed=stuff&tag=<?=urlencode($tag['value'])?>"><?=$tag['value']?></a></span>
              <?endforeach?>
            </p>
          </article>
        </li>
      <?endforeach?>
    </ul>
  </body>
</html>