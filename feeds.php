<?
session_start();
require_once('vendor/init.php');

function feed_consume($ep){

  $vals = array("rdf:type" => "blog:Consumption", "as:published" => "?date");
  $q = query_select_s_where($vals, 10, "date");

  $r = execute_query($ep, $q);
  if($r){
    $posts = construct_uris($ep, select_to_list($r, array("uri")));
  }

  return $posts;
}

function feed_acquire($ep){
  
  $vals = array("rdf:type" => "blog:Acquisition", "as:published" => "?date");
  $q = query_select_s_where($vals, 10, "date");

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
      $posts = feed_acquire($ep);
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
  </head>
  <body>
    <h1>Sloph</h1>
    <?foreach($posts as $uri => $post):?>
      <article>
        <h2><?=$uri?></h2>
        <?foreach($post as $k => $vs):?>
          <p><strong><?=$k?>: </strong>
           <?foreach($vs as $v):?>
              <?=$v['value']?>, 
           <?endforeach?>
          </p>
        <?endforeach?>
      </article>
    <?endforeach?>
  </body>
</html>