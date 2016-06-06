<?
session_start();
require_once('vendor/init.php');

$files = scandir("data");
$posts = array();
foreach($files as $file){
  try{
    $ttl = file_get_contents("data/".$file);
    $graph = new EasyRdf_Graph();
    $graph->parse($ttl, 'ttl');
    //var_dump($graph);
    $posts[] = $graph->serialise('php');
  }catch(Exception $e){
    echo $file;
  }
}

// $q = query_select_s_desc(100);
// $r = execute_query($ep, $q);
// if($r){
//   $posts = construct_uris($ep, select_to_list($r, array("uri")));
// }

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