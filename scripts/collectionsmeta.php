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
  $uris = array("rdf:type", "as:tag", "as:item", "as:image");
  $dates = array("as:published", "as:updated");

  $g = new EasyRdf_Graph($uri);
  
  foreach($data as $pred => $obj){

    if(!is_array($obj)){
      $obj = array($obj);
    }

    $delq = query_delete_objects($uri, $pred);
    $delres = execute_query($ep, $delq);
    if(!$delres){
        echo "Delete for $uri/$pred failed";
    }

    if(empty($obj)){
      unset($obj);
    }
    if(is_array($obj)){
      $obj = array_filter($obj);
    }

    foreach($obj as $o){

      if($pred == "as:tag"){
          $o = "https://rhiaro.co.uk/tags/".$o;
      }
      
      if(in_array($pred, $dates)){
        $dateresource = new EasyRdf_Literal_DateTime($o);
        // $dateresource->create($o);
        $g->addLiteral($uri, $pred, $dateresource);
      }elseif(in_array($pred, $uris)){
        $g->addResource($uri, $pred, $o);
      }else{
        $g->add($uri, $pred, $o);
      }
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
      <h2 id="<?=$uri?>"><a href="/scripts/localedit.php?uri=<?=$uri?>" target="_blank"><?=$uri?></a></h2>
      <form action="#<?=$uri?>">
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
        <p>
            <? $tags = get_values(array($uri => $data), "as:tag");?>
            <?if(isset($tags)):?>
                <?foreach($tags as $tag):?>
                    <input type="text" name="as:tag[]" value="<?=str_replace("https://rhiaro.co.uk/tags/", "", $tag)?>" />
                <?endforeach?>
            <?endif?>
            <input type="text" name="as:tag[]" placeholder="+ tag" />
        </p>
        <p>Items: <?=count(get_values(array($uri => $data), "as:items"))?></p>
        <p><input type="text" placeholder="last updated" name="as:updated" value="<?=last_updated(array($uri => $data))?>" /></p>
        <p><input type="text" placeholder="cover image" name="as:image" value="<?=get_value(array($uri => $data), "as:image")?>" /> (<a href="<?=$uri?>" target="_blank">>></a>)</p>
        <input name="submit" type="submit" value="Save" />
      </form>
      <hr/>
    <?endforeach?>
  </article>
  </body>
</html>