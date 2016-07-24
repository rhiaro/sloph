<?
session_start();
require_once('vendor/init.php');

$posts = array();
$graph = new EasyRdf_Graph();

$q = query_select_s_desc(100);
$r = execute_query($ep, $q);
if($r){
  $posts = construct_uris($ep, select_to_list($r, array("uri")));
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
        <h2><a href="<?=$uri?>"><?=$uri?></a></h2>
        <?foreach($post as $k => $vs):?>
          <p><strong><?=$k?>: </strong>
           <?foreach($vs as $v):?>
              <?=var_dump($v)?>, 
           <?endforeach?>
          </p>
        <?endforeach?>
      </article>
    <?endforeach?>
  </body>
</html>