<?
require_once('../vendor/init.php');

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

function update_album_date($ep, $album, $date){
  // TODO - only update if it's in the future (backfilling photos upsets this)
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

  if(!isset($_SESSION['items']) && isset($_GET['collection'])){
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
    if(isset($_POST['content'])){ $content = $_POST['content']; }else{ $content = ""; }
    $addq = query_insert_add($uri, $collection, $items, $published, $summary, $content);
    $addr = execute_query($ep, $addq);
    if($addr){
      echo "saved";
      $updatealbum = update_album_date($ep, $collection, $published);
    }else{
      var_dump(htmlentities($addq));
    }
  }

  // Get all add posts
  $q = query_select_s_where(array("rdf:type" => "as:Add"), 0);
  $res = execute_query($ep, $q);
  $adds = array();
  foreach($res['rows'] as $r){
    $qa = query_construct($r['s']);
    $ra = execute_query($ep, $qa);
    $adds[$r['s']] = $ra[$r['s']];
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
    input[type=text], textarea { width: 50%; padding: 0.6em; }
    </style>
  </head>
  <body>

    <nav>
      <p>
        <a href="?add=1">Add</a> | <a href="?">Attach</a>
      </p>
    </nav>

    <?if(isset($_GET['add'])):?>

      <h1>Add</h1>

      <form>
        <p>
          <label for="collection">Collection</label>: <input type="text" value="<?=isset($_GET['collection']) ? $_GET['collection'] : "https://i.amy.gy/"?>" id="collection" name="collection" />
          <input type="submit" value="Fetch" name="add" />
        </p>
      </form>

      <form class="w1of1" method="post" id="adds">

        <p><label for="published">Published</label>: <input type="text" value="<?=$now->format(DATE_ATOM)?>" name="published" id="published" /></p>
        <p><label for="uri">URI</label>: <input type="text" value="https://rhiaro.co.uk/<?=$now->format("Y")?>/<?=$now->format("m")?>/<?=uniqid()?>" name="uri" id="uri" /></p>

        <p><label for="content">Content</label></p>
        <p><textarea id="content" name="content" ></textarea></p>

        <p><label for="collection">Collection</label>: <input type="text" value="<?=isset($_GET['collection']) ? $_GET['collection'] : ""?>" name="collection" id="collection" /></p>

        <p><input type="submit" value="Engage" name="engage" /></p>

        <?if(isset($_SESSION['items'])):?>
          <?foreach($_SESSION['items'] as $item):?>
            <?
            if(isset($adds)){
              $added = array();
              foreach($adds as $add){
                if(in_add($item, $add)){
                  $added[] = $item;
                  break;
                }
              }
            }
            ?>
            <div style="float:left;<?=!in_array($item, $added) ? " font-weight: bold;" : ""?>">
              <p><input type="checkbox" name="items[]" value="<?=$item?>" id="<?=$item?>" /> <label for="<?=$item?>"><a href="<?=$item?>"><?=$item?></a> <br/>
              <?if(!in_array($item, $added)):?>
                <img src="<?=$_IMG?>200/0/<?=$item?>" />
              <?endif?>
              </label></p>
            </div>
          <?endforeach?>
        <?endif?>

        <p><input type="submit" value="Engage" name="engage" /></p>

      </form>
      <? //var_dump($adds);?>
      <?if(isset($adds)):?>
        <ul>
          <?foreach($adds as $auri => $add):?>
            <li><a href="<?=$auri?>"><?=isset($add["https://www.w3.org/ns/activitystreams#summary"]) ? $add["https://www.w3.org/ns/activitystreams#summary"][0]["value"] : $auri?></a></li>
          <?endforeach?>
        </ul>
      <?endif?>

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
  
  </body>
</html>