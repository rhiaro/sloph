<?
session_start();
require_once('../vendor/init.php');
require_once('Parsedown.php');

$now = new DateTime();

if(isset($_GET['reset'])){ unset($_SESSION[$_GET['reset']]); }
if(isset($_GET['graph'])){ $graph = $_GET['graph']; }else{ $graph = "https://blog.rhiaro.co.uk/"; }

function graphs($ep){
  $q = query_get_graphs();
  $res = execute_query($ep, $q);
  $graphs = array();
  if($res){
    foreach($res["rows"] as $row){
      $graphs[] = $row["g"];
    }
  }
  return $graphs;
}

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
      "https://www.w3.org/ns/activitystreams#" => array("Actor", "Person", "Note", "Article", "Profile", "Organization", "Event", "Arrive", "Activity", "Object", "Like", "Announce", "Add", "Travel", "Accept", "Place", "Relationship", "Collection"),
      "https://terms.rhiaro.co.uk/as#" => array("Consume", "Acquire", "Sleep")
    );
  $out = '<p><label><strong>+ type</strong>: </label>';
  $out .= '  <select name="data[http://www.w3.org/1999/02/22-rdf-syntax-ns#type]['.$i.'][value]">';
  $out .= '    <option value="">none</option>';
  foreach($types as $base => $frags){
    foreach($frags as $type){
      $out .= '    <option value="'.$base.$type.'">'.$type.'</option>';
    }
  }
  $out .= '  </select>';
  $out .= '<input type="hidden" name="data[http://www.w3.org/1999/02/22-rdf-syntax-ns#type]['.$i.'][type]" value="uri" /></p>';
  return $out;
}

function plusproperty(){
  $properties = array(
      "https://www.w3.org/ns/activitystreams#" => array("name", "published", "updated", "summary", "content", "startTime", "endTime", "image", "inReplyTo", "location", "tag", "url", "to", "bto", "cc", "bcc", "duration", "actor", "object", "target", "origin", "result", "items", "relationship", "outbox"),
      "https://terms.rhiaro.co.uk/as#" => array("cost"),
      "https://terms.rhiaro.co.uk/view#" => array("banality", "intimacy", "tastiness", "informative", "wanderlust", "css", "color")
    );
  $out = '<p>';
  $out .= '  <label><select name="data[new]">';
  foreach($properties as $base => $props){
    foreach($props as $prop){
      $out .= '    <option value="'.$base.$prop.'">'.$prop.'</option>';
    }
  }
  $out .= '  </select></label>';
  $out .= '  <input type="text" name="data[newvalue]" />';
  $out .= '  <select name="data[newtype]">';
  $out .= '    <option value="literal">lit</option>
                <option value="uri">uri</option>
              </select>
              <select name="data[newdatatype]">
                <option value="">none</option>
                <option value="http://www.w3.org/2001/XMLSchema#dateTime">dateTime</option>
              </select>';
  $out .= '</p>';
  return $out;
}

function process_new($data){
  $prop = $data['new'];
  $val = $data['newvalue'];
  $type = $data['newtype'];
  if(isset($data['newdatatype']) && !empty($data['newdatatype'])){
    $datatype = $data['newdatatype'];
  }
  if(isset($data[$prop])){
    $i = count($data[$prop]);
  }else{
    $i = 0;
  }
  $data[$prop][$i]['value'] = $val;
  $data[$prop][$i]['type'] = $type;
  if(isset($datatype)){
    $data[$prop][$i]['datatype'] = $datatype;
  }
  unset($data['new']);
  unset($data['newvalue']);
  unset($data['newtype']);
  unset($data['newdatatype']);
  return $data;
}

function delete($ep, $uri, $graph){
  $q = query_remove_from_graph($uri, $graph);
  $r = execute_query($ep, $q);
  return $r;
}

function insert($ep, $turtle, $graph){
  $q = query_insert($turtle, $graph);
  $r = execute_query($ep, $q);
  return $r;
}

if(isset($_POST['savenew'])){
  $uri = trim($_POST['uri']);
  $rdfphp[$uri] = process_new($_POST['data']);
  $newgraph = new EasyRdf_Graph($uri);
  $newgraph->parse($rdfphp, 'php');
  $turtle = $newgraph->serialise('ntriples');
  $ins = insert($ep, $turtle, $graph);
  if($ins){
    $result[$uri] = "Updated";
    header('Location: ?uri='.$uri.'&graph='.$graph);
  }else{
    $result[$uri] = "Error: could not insert";
  }
}
elseif(isset($_POST['data']) && count($_POST['data']) > 0){
  $_POST = process_new($_POST['data']);
  $newgraph = new EasyRdf_Graph($_POST['uri']);
  $uri = $_POST['uri'];
  unset($_POST['uri']);
  $rdfphp[$uri] = remove_empty($_POST);
  $newgraph->parse($rdfphp, 'php');
  $turtle = $newgraph->serialise('ntriples');
  $del = delete($ep, $uri, $graph);
  if($del){
    $ins = insert($ep, $turtle, $graph);
    if($ins){
      $result[$uri] = "Updated";
    }else{
      $result[$uri] = "Error: could not insert";
    }
  }else{
    $result[$uri] = "Error: could not delete";
  }
}


$q = query_select_s(0, $graph);
if(isset($_GET['flag'])){
  if($_GET['flag'] == "doublecontents"){
    $q = "PREFIX as: <https://www.w3.org/ns/activitystreams#> .
SELECT ?s WHERE {
  ?s as:content ?con1 .
  ?s as:content ?con2 .
  filter(?con1 != ?con2) .
}";
  }elseif($_GET['flag'] == "doubledate"){
    $q = "PREFIX as: <https://www.w3.org/ns/activitystreams#> .
SELECT ?s WHERE {
  ?s as:published ?con1 .
  ?s as:published ?con2 .
  filter(?con1 != ?con2) .
}";
  }
}
if(isset($_GET['uri'])){
  $_SESSION['uris'] = array($_GET['uri']);
}

if(!isset($_SESSION['uris'])){
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
$posts = construct_uris_in_graph($ep, $uris, $graph);
$graphs = graphs($ep);

if(isset($_GET['flag']) && $_GET['flag'] == "notype"){
  $untyped_uris = array();
  $untyped_posts = array();
  foreach($posts as $uri => $post){
    if(!isset($post['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'])){
      $untyped_uris[] = $uri;
      $untyped_posts[$uri] = $post;
    }
  }
  $_SESSION['uris'] = $untyped_uris;
  $posts = $untyped_posts;
}

if(isset($_GET['uri']) && count($posts) < 1){
  $uri_for_new = $_GET['uri'];
}else{
  $uri_for_new = "https://rhiaro.co.uk/".$now->format("Y")."/".$now->format("m")."/".uniqid();
}

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
      <p>
        <strong>Graph: <?=$graph?></strong> 
        <form id="changegraph">
          <select name="graph">
            <?foreach($graphs as $g):?>
              <option name="graph" value="<?=$g?>"><?=$g?></option>
            <?endforeach?>
          </select>
          <?if(isset($_GET['uri'])):?>
            <input type="hidden" name="uri" value="<?=$_GET['uri']?>" />
          <?endif?>
          <input type="hidden" name="reset" value="uris" />
          <input type="submit" value="Change" />
        </form>
        </p>
      <p>Resources <?=$offset?> to <?=$offset+$length?> of <?=count($_SESSION['uris'])?> | <a href="?offset=<?=$offset-$length?>">prev</a> | <a href="?offset=<?=$offset+$length?>">next</a> | <a href="?reset=uris">reset</a> | <a href="#new">new</a></p>
    </div>
    <?if(isset($_GET['uri'])):?>
      <p><strong>URI: </strong><?=$_GET['uri'];?></p>
      <?if(count($posts) < 1):?>
        <p>No triples in this graph for this URI.. make some!</p>
      <?endif?>
    <?endif?>
    <?foreach($posts as $uri => $post):?>
      <form id="<?=$uri?>" method="post" action="#<?=$uri?>">
        <p><a href="<?=$uri?>"><?=$uri?></a> <input type="submit" value="Save"/></p>
        <input type="hidden" value="<?=$uri?>" name="data[uri]" />
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
                <textarea name="data[<?=$k?>][<?=$i?>][value]"><?=htmlentities($v['value'])?></textarea>
              <?else:?>
                <input name="data[<?=$k?>][<?=$i?>][value]" type="text" value="<?=htmlentities($v['value'])?>" style="width: <?=strlen($v['value']) * 8?>px; max-width: 100%" />
              <?endif?>
              <input name="data[<?=$k?>][<?=$i?>][type]" type="hidden" value="<?=$v["type"]?>" style="width: <?=strlen($v['datatype']) * 8?>px; max-width: 100%" />
              <?if(isset($v["datatype"])):?>
                <input name="data[<?=$k?>][<?=$i?>][datatype]" type="text" value="<?=$v["datatype"]?>" style="width: <?=strlen($v['datatype']) * 8?>px; max-width: 100%" />
              <?endif?>
          <?endforeach?>
          </p>
          <p>
            <?if($k == "http://www.w3.org/1999/02/22-rdf-syntax-ns#type"):?>
              <?=plustype($i+1);?>
            <?else:?>
              <label><strong>+</strong></label>
              <input type="text" name="data[<?=$k?>][<?=count($vs)?>][value]" style="width: 8em;" />
              <select name="data[<?=$k?>][<?=count($vs)?>][type]">
                <option value="literal">lit</option>
                <option value="uri">uri</option>
              </select>
              <select name="data[<?=$k?>][<?=count($vs)?>][datatype]">
                <option value="">none</option>
                <option value="http://www.w3.org/2001/XMLSchema#dateTime">dateTime</option>
              </select>
            <?endif?>
          </p>
        <?endforeach?>
        <?=plusproperty()?>
      </form>
      <hr/>
    <?endforeach?>

    <form id="new" method="post">
      <p>
        <label for="uri">URI: </label>
        <input type="text" name="uri" value="<?=$uri_for_new?>" /> 
        <input type="submit" value="Create" name="savenew"/>
      </p>
      <?=plustype()?>
      <?=plusproperty()?>
    </form>

    <div class="info">
      <p><strong>Graph: <?=$graph?></strong></p>
      <p>Resources <?=$offset?> to <?=$offset+$length?> of <?=count($_SESSION['uris'])?> | <a href="?offset=<?=$offset-$length?>">prev</a> | <a href="?offset=<?=$offset+$length?>">next</a> | <a href="?reset=uris">reset</a></p>
    </div>
  </body>
</html>