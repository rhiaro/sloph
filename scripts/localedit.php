<?
session_start();
require_once('../vendor/init.php');

if(isset($_GET['reset'])){ unset($_SESSION[$_GET['reset']]); }

function remove_empty($haystack){
  foreach ($haystack as $property => $values) {
    if (is_array($values)) {
      foreach($values as $k => $item)
        if(empty($item["value"])){
          unset($haystack[$property][$k]);
        }
    }

    if (empty($haystack[$property])) {
        unset($haystack[$property]);
    }
  }
  return $haystack;
}

function plustype($i=0){
  $types = array(
      "http://www.w3.org/ns/activitystreams#" => array("Actor", "Person", "Note", "Article", "Profile", "Organization", "Event", "Arrive", "Activity", "Object", "Like", "Announce", "Add", "Travel", "Accept", "Place", "Collection"),
      "http://vocab.amy.so/blog#" => array("Consumption", "Acquisition")
    );
  $out = '<p><label><strong>+ type</strong>: </label>';
  $out .= '  <select name="http://www.w3.org/1999/02/22-rdf-syntax-ns#type['.$i.'][value]">';
  $out .= '    <option value="">none</option>';
  foreach($types as $base => $frags){
    foreach($frags as $type){
      $out .= '    <option value="'.$base.$type.'">'.$type.'</option>';
    }
  }
  $out .= '  </select>';
  $out .= '<input type="hidden" name="http://www.w3.org/1999/02/22-rdf-syntax-ns#type['.$i.'][type]" value="uri" /></p>';
  return $out;
}

if(count($_POST) > 0){
  $newgraph = new EasyRdf_Graph($_POST['uri']);
  $uri = $_POST['uri'];
  unset($_POST['uri']);
  $rdfphp[$uri] = remove_empty($_POST);
  $newgraph->parse($rdfphp, 'php');
  $result[$uri] = $newgraph->serialise('turtle');
}

if(!isset($_SESSION['uris'])){
  $q = query_select_s();
  $r = execute_query($ep, $q);
  if($r){
    $_SESSION['uris'] = select_to_list($r, array("uri"));
  }
}

if(isset($_GET['offset']) && is_numeric($_GET['offset'])){
  $offset = $_GET['offset'];
}else{
  $offset = 0;
}
if(isset($_GET['length']) && is_numeric($_GET['length'])){
  $length = $_GET['length'];
}else{
  $length = 20;
}

$uris = array_slice($_SESSION['uris'], $offset, $length);
$posts = array();
$posts = construct_uris($ep, $uris);

?>
<!doctype html>
<html>
  <head>
    <title>Local edit</title>
    <style>
      label { width: 32em; display: inline-block; text-align: right; }
      pre { max-height: 32em; overflow: auto; float: left; border: 1px solid silver; }
      input, textarea { max-width: 100%; border: 1px solid silver; padding: 0.4em; }
      textarea { width: 72em; height: 16em; }
      .info { background-color: #abcdef; padding: 0.4em; font-family: sans-serif; }
      hr { border: 2px solid #abcdef; }
    </style>
  </head>
  <body>
    <div class="info">
      <p>Resources <?=$offset?> to <?=$offset+$length?> of <?=count($_SESSION['uris'])?> | <a href="?offset=<?=$offset-$length?>">prev</a> | <a href="?offset=<?=$offset+$length?>">next</a> | <a href="?reset=uris">reset</a></p>
    </div>
    <?foreach($posts as $uri => $post):?>
      <form id="<?=$uri?>" method="post" action="#<?=$uri?>">
        <p><a href="<?=$uri?>"><?=$uri?></a> <input type="submit" value="Save"/></p>
        <input type="hidden" value="<?=$uri?>" name="uri" />
        <?if(isset($result[$uri])):?>
          <div style="overflow:hidden; width: 100%;">
            <pre>
              <?=htmlentities($result[$uri])?>
            </pre>
          </div>
        <?endif?>
        <?if(!isset($post['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'])):?>
          <?=plustype()?>
        <?endif?>
        <?foreach($post as $k => $vs):?>
          <p><label><?=$k?>: </label>
           <?foreach($vs as $i => $v):?>
              <?if(strlen($v['value']) > 120):?>
                <textarea name="<?=$k?>[<?=$i?>][value]"><?=$v['value']?></textarea>
              <?else:?>
                <input name="<?=$k?>[<?=$i?>][value]" type="text" value="<?=$v['value']?>" style="width: <?=strlen($v['value']) * 8?>px; max-width: 100%" />
              <?endif?>
              <input name="<?=$k?>[<?=$i?>][type]" type="hidden" value="<?=$v["type"]?>" />
              <?if(isset($v["datatype"])):?>
                <input name="<?=$k?>[<?=$i?>][datatype]" type="text" value="<?=$v["datatype"]?>" />
              <?endif?>
          <?endforeach?>
          </p>
          <p>
            <?if($k == "http://www.w3.org/1999/02/22-rdf-syntax-ns#type"):?>
              <?=plustype($i+1);?>
            <?else:?>
              <label><strong>+</strong></label>
              <input type="text" name="<?=$k?>[<?=count($vs)?>][value]" style="width: 8em;" />
              <select name="<?=$k?>[<?=count($vs)?>][type]">
                <option value="literal">lit</option>
                <option value="uri">uri</option>
              </select>
              <select name="<?=$k?>[<?=count($vs)?>][datatype]">
                <option value="">none</option>
                <option value="http://www.w3.org/2001/XMLSchema#dateTime">dateTime</option>
              </select>
            <?endif?>
          </p>
        <?endforeach?>
      </form>
      <hr/>
    <?endforeach?>
  </body>
</html>