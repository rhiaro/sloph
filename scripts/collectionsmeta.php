<?
require_once('../vendor/init.php');

function get_add_targets($ep){
  $q = query_construct_collections_from_adds();
  $res = execute_query($ep, $q);
  krsort($res);
  return $res;
}

function get_collections($ep){

}

function last_updated($graph){
  $updated = get_values($graph, "as:updated");
  rsort($updated);
  return $updated[0];
}

function replace_properties($ep, $uri, $data){

  unset($data["submit"]);
  unset($data["uri"]);

  $lits = array("as:name", "as:content", "as:summary");
  $uris = array("rdf:type", "as:tag", "as:item");

  $g = new EasyRdf_Graph($uri);
  foreach($data as $key => $property){
    if(empty($property)){
      unset($data[$key]);
    }
  }

  foreach($data as $pred => $obj){

    if(!is_array($obj)){
      $obj = array($obj);
    }
    foreach($obj as $o){
      if(in_array($pred, $lits)){
        $g->add($uri, $pred, $o);
      }elseif(in_array($pred, $uris)){
        $g->addResource($uri, $pred, $o);
      }
    }
    $delq = query_delete_objects($uri, $pred);
    // var_dump(htmlentities($delq));
    $delres = execute_query($ep, $delq);
    if(!$delres){
        echo "Delete for $uri/$pred failed";
    }
  }
  $turtle = $g->serialise('ntriples');
  $insq = query_insert($turtle);
  $insr = execute_query($ep, $insq);
  if(!$insr){
    echo "Insert for $uri failed";
    echo $g->dump();
  }
}

if(isset($_GET['submit'])){
  $update_uri = $_GET['uri'];
  $replace = replace_properties($ep, $update_uri, $_GET);
}

$collectiontypes = array(
    "https://www.w3.org/ns/activitystreams#" => array("Collection", "Group"),
    "https://terms.rhiaro.co.uk/as#" => array("Album"));

$addtargets = get_add_targets($ep);
// $g = new EasyRdf_Graph();
// $g->parse($addtargets, 'php');

?>

<!doctype html>
<html>
  <head>
  <title>Collections stuff</title>
  <link rel="stylesheet" type="text/css" href="/views/normalize.min.css" />
  <link rel="stylesheet" type="text/css" href="/views/core.css" />
  </head>
  <body>
  <article class="wrapper">
    <h1>Collections</h1>
    <?foreach($addtargets as $uri => $data):?>
      <h2><a href="/scripts/localedit.php?uri=<?=$uri?>" target="_blank"><?=$uri?></a></h2>
      <form>
        <input type="hidden" name="uri" value="<?=$uri?>" />
        <h3>
          <input name="as:name" placeholder="Name" type="text" value="<?=get_value(array($uri => $data), "as:name")?>" />
        </h3>
        <p>
          <?foreach(get_values(array($uri => $data), "rdf:type") as $type):?>
            <input type="text" name="rdf:type[]" value="<?=$type?>" />   
          <?endforeach?>
          <select name="rdf:type[]">
            <option value="">none</option>
            <?foreach($collectiontypes as $base => $props):?>
              <?foreach($props as $prop):?>
                <option value="<?=$base.$prop?>"><?=$prop?></option>
              <?endforeach?>
            <?endforeach?>
            ?>
          </select>
        </p>
        <p>
          <textarea placeholder="summary" name="as:summary"><?=get_value(array($uri => $data), "as:summary")?></textarea>
        </p>
        <p>
          <textarea placeholder="content" name="as:content"><?=get_value(array($uri => $data), "as:content")?></textarea>
        </p>
        <p>Items: <?=count(get_values(array($uri => $data), "as:items"))?></p>
        <p>Last updated: <?=last_updated(array($uri => $data))?></p>
        <input name="submit" type="submit" value="Save" />
      </form>
      <hr/>
    <?endforeach?>
  </article>
  </body>
</html>