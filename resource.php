<?
session_start();
require_once('vendor/init.php');

function get($ep, $uri){
  global $_PREF;
  $q = query_construct($uri);
  $r = execute_query($ep, $q);
  if(empty($r)){
    // Nothing found, try to find something which isPrimaryTopicOf this URI instead.
    $q = query_select_hasPrimaryTopic($uri);
    $hpts = execute_query($ep, $q);
    if(!empty($hpts)){
      $uris = select_to_list($hpts);
      $r = construct_uris($ep, $uris);
      if(!empty($r)){
        foreach($uris as $hpt){
          $r[$uri][$_PREF['foaf']."hasPrimaryTopic"][] = array("value" => $hpt, "type" => "uri");
        }
      }
    }
  }
  return $r;  
}

if(isset($_GET['resource'])){
  $resource = urldecode($_GET['resource']);

  $r = get($ep, $resource);
  if(empty($r)){
    $message["404"] = "There is no resource here going by that identifier.";
  }else{
    $post = $r[$resource];
  }

  if(isset($_GET['ct'])){
    $ct = $_GET['ct'];
  }else{
    $ct = "text/html";
  }

  // Conneg
  $graph = new EasyRdf_Graph($resource);
  $graph->parse($r, 'php', $resource);
  try{
    $format = EasyRdf_Format::getFormat($ct);
    if($format->getSerialiserClass()){
      $out = $graph->serialise($ct);
      header('Content-Type: '.$format->getDefaultMimeType());
      echo $out;
      exit;
    }
  }catch(Exception $e){
    if($ct == "activity"){
      header('Content-Type: application/activity+json');
      echo graph_to_as2($graph);
      exit;
    }
  }

}else{
  $message["Beyond the final frontier"] = "You shouldn't be here.";
}

?>
<!doctype html>
<html>
  <head>
    <title>Sloph</title>
  </head>
  <body>
    <h1>Sloph</h1>
    <article>
      <h2><?=$resource?></h2>
      <?if(!empty($message)):?>
        <?foreach($message as $k => $m):?>
          <p><strong><?=$k?>: </strong><?=$m?></p>
        <?endforeach?>
      <?endif?>
      <?if(isset($post)):?>
        <?foreach($post as $k => $vs):?>
          <?if($k == $_PREF['foaf']."hasPrimaryTopic"):?>
            <?foreach($vs as $v):?>
              <?foreach($r[$v['value']] as $k => $vs):?>
                <p><strong><?=$k?>: </strong>
                 <?foreach($vs as $v):?>
                    <?=$v['value']?>, 
                 <?endforeach?>
                </p>
              <?endforeach?>
            <?endforeach?>
          <?else:?>
            <p><strong><?=$k?>: </strong>
             <?foreach($vs as $v):?>
                <?=$v['value']?>, 
             <?endforeach?>
            </p>
          <?endif?>
        <?endforeach?>
      <?endif?>
    </article>
  </body>
</html>