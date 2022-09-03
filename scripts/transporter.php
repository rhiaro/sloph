<?
ini_set('memory_limit', '256M');
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once('../vendor/init.php');
require_once('../vendor/sloph/outbox_side_effects.php');

$now = new DateTime();
$posts = array();

function fetch_album($url){
  curl_setopt_array($ch = curl_init(), array(
    CURLOPT_URL => $url,
    CURLOPT_HTTPHEADER => array("Accept" => "application/ld+json"),
    CURLOPT_RETURNTRANSFER => 1
  ));
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

function fetch_albums(){
  $r = fetch_album("https://i.amy.gy/");
  $c = json_decode($r, true);
  return $c["items"];
}

function update_album_date($ep, $album, $date){
  $delq = query_delete_objects($album, "as:updated");
  $delr = execute_query($ep, $delq);
  if($delr){
    $insq = query_insert_lit($album, "as:updated", $date, "xsd:dateTime");
    $insr = execute_query($ep, $insq);
    if($insr){
      return true;
    }
    return false;
  }
  return false;
}

function rhiaro_url_to_date($url){
  $str = explode("/", $url);
  return array("m" => $str[4], "y" => $str[3]);
}

function img_url_to_date($url){
  $i = explode("/", $url);
  $fn = $i[4];
  $dt = str_replace("IMG_", "", $fn);
  $dt = str_replace(".jpg", "", $dt);
  $y = intval(substr($dt, 0, 4));
  $m = intval(substr($dt, 4, 2));
  $d = intval(substr($dt, 6, 2));
  $h = intval(substr($dt, 9, 2));
  $i = intval(substr($dt, 11, 2));
  $s = intval(substr($dt, 13, 2));
  $date = new DateTime();
  $date->setDate($y, $m, $d);
  $date->setTime($h, $i, $s);
  return $date;
}

function in_collection($resource, $collection){
  if(!is_array($collection)){
    // todo: construct
  }else{
    foreach($collection["https://www.w3.org/ns/activitystreams#items"] as $i){
      if($resource == $i['value']){
        return true;
      }
    }
  }
  return false;
}

function in_add($resource, $add){
  if(!is_array($add)){
    // todo: construct
  }else{
    foreach($add["https://www.w3.org/ns/activitystreams#object"] as $i){
      if($resource == $i['value']){
        return true;
      }
    }
  }
  return false;
}

function get_objects_added($ep){
  $q = query_select_image(null,0);
  $r = execute_query($ep, $q);
  $added = select_to_list($r);
  return $added;
}

function make_tags($input_array){
  $base = "https://rhiaro.co.uk/tags/";
  $tags_string = $input_array["string"];
  unset($input_array["string"]);
  $tags = explode(",", $tags_string);
  foreach($tags as $tag){
    if(strlen(trim($tag)) > 0){
        $input_array[] = $base.urlencode(trim($tag));
    }
  }
  return $input_array;
}

if(isset($_GET['reset'])){
  unset($_SESSION);
}

/*********************************************************************************/
/* Add to Acquire posts */

if(!isset($_GET['month'])){
  $_GET['month'] = strtolower($now->format("F"));
}
if(!isset($_GET['year'])){
  $_GET['year'] = $now->format("Y");
}
$mn = new DateTime("00:00:00 1st ".$_GET['month']." ".$_GET['year']);

// Process
if(isset($_GET['engage'])){
  $insq = get_prefixes()."\nINSERT INTO <https://blog.rhiaro.co.uk/> { <".$_GET['post']."> as:image <".$_GET['image']."> . }";
  $insr = execute_query($ep, $insq);
  if($insr){
    echo "saved";
  }else{
    echo "fail";
    var_dump($insr);
  }
}

// Get all acquire posts in one month
$q = query_select_s_where(array("rdf:type" => "asext:Acquire"), 0);
$res = execute_query($ep, $q);
foreach($res['rows'] as $r){
  $m = rhiaro_url_to_date($r['s'])["m"];
  $y = rhiaro_url_to_date($r['s'])["y"];
  if($m == $mn->format("m") && $y == $mn->format("Y")){
    $qc = query_construct($r['s']);
    $rc = execute_query($ep, $qc);
    $posts[$r['s']] = $rc[$r['s']];
  }
}

// Get all images for that month plus one week after
$images = array();
$response = fetch_album("https://i.amy.gy/obtainium/");
$collection = json_decode($response, true);
foreach($collection['items'] as $img){
  $date = img_url_to_date($img);
  if($date->format("m") == $mn->format("m") && $date->format("y") == $mn->format("y")){
    // Check not already used
    $checkq = get_prefixes()."\nSELECT ?s WHERE { ?s as:image <".$img."> }";
    $checkr = execute_query($ep, $checkq);
    if(count($checkr['rows']) < 1){
        $images[] = $img;
    }
  }
}

/*********************************************************************************/
/* Create Add Activities */

if(isset($_GET['add'])){

  $added = get_objects_added($ep);
  $tags = array(
                "foraging" => "https://rhiaro.co.uk/tags/foraging",
                "food" => "https://rhiaro.co.uk/tags/food",
                "vegan" => "https://rhiaro.co.uk/tags/vegan",
                "gardening" => "https://rhiaro.co.uk/tags/gardening",
                "diy" => "https://rhiaro.co.uk/tags/diy",
                "crochet" => "https://rhiaro.co.uk/tags/crochet",
                "fife" => "https://rhiaro.co.uk/tags/fife",
                "travel" => "https://rhiaro.co.uk/tags/travel",
                "hiking" => "https://rhiaro.co.uk/tags/hiking",
                "walkies" => "https://rhiaro.co.uk/tags/walkies"
              );

  if((!isset($_SESSION['items']) && isset($_GET['collection'])) || $_GET['add'] == 'Fetch'){
    $response = fetch_album($_GET['collection']);
    $collection = json_decode($response, true);
    rsort($collection['items']);
    $_SESSION['items'] = $collection['items'];
  }

  if(isset($_POST['engage'])){
    $collection = $_POST['collection'];
    $items = $_POST['items'];
    $uri = $_POST['uri'];
    $published = $_POST['published'];
    $summary = "Amy added ".count($items)." photos to ".$collection;
    $post_tags = make_tags($_POST['tags']);
    if(isset($_POST['content'])){ $content = $_POST['content']; }else{ $content = ""; }

    $addq = query_insert_add($uri, $collection, $items, $published, $summary, $post_tags, $content);
    $addr = execute_query($ep, $addq);
    if($addr){
      $success = true;
      $updatealbum = update_album_date($ep, $collection, $published);
      $post_graph = new EasyRdf_Graph($uri);
      foreach($post_tags as $tag){
        $post_graph->addResource($uri, "as:tag", $tag);
      }
      $updatetags = update_tags_collection($ep, $post_graph);
      unset($_POST);
    }else{
      $success = false;
      var_dump(htmlentities($addq));
    }
  }

  if(!isset($_SESSION['albums'])){
    // Get albums as seen by i.amy.gy
    $albums = fetch_albums();

    // Get albums as seen by rhiaro.co.uk
    $q_cols = query_construct_collections_from_adds();
    $r_cols = execute_query($ep, $q_cols);
    unset($r_cols["https://rhiaro.co.uk/bookmarks/"]);

    $sorted = array();
    foreach($albums as $album){
      if(array_key_exists($album["id"], $r_cols)){
        $added = get_values(array($album["id"] => $r_cols[$album["id"]]), "as:items");
      }else{
        $added = array();
      }
      if(count($added) < $album["totalItems"]){
        $sorted[$album["id"]] = array(
          "total" => $album["totalItems"],
          "added" => count($added),
          "updated" => $album["updated"]
        );
      }
    }
    uasort($sorted, function ($a, $b) {
      return strtotime($b["updated"]) - strtotime($a["updated"]);
    });
    $_SESSION['albums'] = $sorted;
  }else{
    $sorted = $_SESSION['albums'];
  }

}

/*********************************************************************************/
?>

<!doctype html>
<html>
  <head>
    <title>transporter..</title>
    <link rel="stylesheet" href="../views/normalize.min.css" />
    <link rel="stylesheet" href="../views/base.css" />
    <link rel="stylesheet" href="../views/core.css" />
    <style type="text/css">
    img { width: 300px; }
    form { padding: 1em; }
    .fields { max-width: 800px; margin-left: auto; margin-right: auto; }
    input[type=text], input[type=submit], textarea { padding: 0.6em; width:  97%; }
    .success { text-align: center; border: 3px solid forestgreen; background-color: palegreen; padding: 1em 0 1em 0; }
    </style>
  </head>
  <body>

    <nav>
      <p>
        <a href="?add=1">Add</a> | <a href="?">Attach</a>
      </p>
    </nav>
    <main>
    <?if(isset($_GET['add'])):?>

      <h1>Add</h1>

      <?if(isset($success) && $success == true):?>
        <p class="success">Saved</p>
      <?endif?>

      <form id="album" class="fields">
        <p>
          <label for="collection">Collection</label>:
          <select id="collection" name="collection">
            <?foreach($sorted as $uri => $counts):?>
              <option value="<?=$uri?>"<?=isset($_GET['collection']) && $uri == $_GET['collection'] ? " selected" : ""?>><?=str_replace("https://i.amy.gy/", "", $uri)?> (<?=$counts["added"]?> out of <?=$counts["total"]?>)</option>
            <?endforeach?>
          </select>
          <input type="submit" value="Fetch" name="add" />
        </p>
      </form>

      <form class="w1of1" method="post" id="adds">
        <div class="fields">
          <p><label for="published">Published</label>: <input type="text" value="<?=$now->format(DATE_ATOM)?>" name="published" id="published" /></p>
          <p><label for="uri">URI</label>: <input type="text" value="https://rhiaro.co.uk/<?=$now->format("Y")?>/<?=$now->format("m")?>/<?=uniqid()?>" name="uri" id="uri" /></p>

          <p><label for="content">Content</label>:
          <textarea id="content" name="content" ></textarea></p>

          <p>
            <label for="tags">Tags</label>
            <input type="text" name="tags[string]" id="tags"<?=(isset($_POST['tags']['string'])) ? ' value="'.$_POST['tags']['string'].'"' : ''?> />
          </p>
          <?if(isset($tags)):?>
            <p>
              <label></label>
              <span>
                <?foreach($tags as $label => $tag):?>
                  <input type="checkbox" value="<?=$tag?>" name="tags[]" id="<?=$label?>"<?=(in_array($tag, $_POST['tags'])) ? " checked" : ""?> /> <label for="<?=$label?>"><?=$label?></label>
                <?endforeach?>
              </span>
            </p>
          <?endif?>

          <p><label for="collection">Collection</label>: <input type="text" value="<?=isset($_GET['collection']) ? $_GET['collection'] : ""?>" name="collection" id="collection" /></p>

          <p><input type="submit" value="Engage" name="engage" /></p>
        </div>

        <?if(isset($_SESSION['items'])):?>
          <?foreach($_SESSION['items'] as $item):?>
            <div style="float:left;<?=!in_array($item, $added) ? " font-weight: bold;" : ""?>">
              <p><input type="checkbox" name="items[]" value="<?=$item?>" id="<?=$item?>" /> <label for="<?=$item?>"><a target="_blank" href="<?=$item?>"><?=$item?></a> <br/>
              <?if(!in_array($item, $added)):?>
                <img src="<?=$_IMG?>200/0/<?=$item?>" />
              <?endif?>
              </label></p>
            </div>
          <?endforeach?>
        <?endif?>

        <p><input type="submit" value="Engage" name="engage" /></p>

      </form>

    <?else:?>
      <h1><a href="?month=<?=$_GET['month']?>"><?=$_GET['month']?></a></h1>

      <form class="w1of1">

        <p><input type="submit" value="Engage" name="engage" /></p>
        <input type="hidden" name="month" value="<?=$_GET['month']?>" />
        <input type="hidden" name="year" value="<?=$_GET['year']?>" />

        <div class="w1of4">
          <div class="inner">

            <?foreach($posts as $uri => $data):?>
              <?if(!isset($data['https://www.w3.org/ns/activitystreams#image'])):?>
                <p><input type="radio" name="post" value="<?=$uri?>" /> <a href="<?=$uri?>" target="_blank"><?=$data['https://www.w3.org/ns/activitystreams#published'][0]['value']?></a></p>
                <p><?=$data['https://www.w3.org/ns/activitystreams#content'][0]['value']?></p>
              <?endif?>
            <?endforeach?>

          </div>
        </div>

        <div class="w3of4">
          <div class="inner">
            <?foreach($images as $image):?>
              <div style="float:left">
                <p><input type="radio" name="image" value="<?=$image?>" /> <?=$image?></p>
                <p><img src="<?=$image?>" title="<?=$image?>" /></p>
              </div>
            <?endforeach?>
          </div>
        </div>

        <p><input type="submit" value="Engage" name="engage" /></p>

      </form>
    <?endif?>
    </main>
  
  </body>
</html>