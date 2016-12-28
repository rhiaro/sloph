<?
require_once('../vendor/init.php');
if(!isset($_GET['month'])){
  $_GET['month'] = "june";
}
$mn = new DateTime("00:00:00 1st ".$_GET['month']);

// Process
if(isset($_GET['engage'])){
  $insq = get_prefixes()."\nINSERT INTO <http://blog.rhiaro.co.uk#> { <".$_GET['post']."> as:image <".$_GET['image']."> . }";
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
  $str = explode("/", $r['s']);
  $m = $str[4];
  if($m == $mn->format("m")){
    $qc = query_construct($r['s']);
    $rc = execute_query($ep, $qc);
    $posts[$r['s']] = $rc[$r['s']];
  }
}

// Get all images for that month plus one week after
$images = array();
curl_setopt_array($ch = curl_init(), array(
  CURLOPT_URL => "https://i.amy.gy/obtainium/",
  CURLOPT_HTTPHEADER => array("Accept" => "application/ld+json"),
  CURLOPT_RETURNTRANSFER => 1
));
$response = curl_exec($ch);
curl_close($ch);
$collection = json_decode($response, true);
foreach($collection['items'] as $img){
  $i = explode("/", $img);
  $fn = $i[4];
  $dt = str_replace("IMG_", "", $fn);
  $dt = str_replace(".jpg", "", $dt);
  $m = substr($dt, 4, 2);
  if($m == $mn->format("m")){

    // Check not already used
    $checkq = get_prefixes()."\nSELECT ?s WHERE { ?s as:image <".$img."> }";
    $checkr = execute_query($ep, $checkq);
    if(count($checkr['rows']) < 1){
        $images[] = $img;
    }
  }
}
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
    </style>
  </head>
  <body>
    <h1><?=$_GET['month']?></h1>

    <form class="w1of1">

      <p><input type="submit" value="Engage" name="engage" /></p>
      <input type="hidden" name="month" value="<?=$_GET['month']?>" />

      <div class="w1of4">
        <div class="inner">

          <?foreach($posts as $uri => $data):?>
            <?if(!isset($data['http://www.w3.org/ns/activitystreams#image'])):?>
              <p><input type="radio" name="post" value="<?=$uri?>" /> <a href="<?=$uri?>" target="_blank"><?=$data['http://www.w3.org/ns/activitystreams#published'][0]['value']?></a></p>
              <p><?=$data['http://www.w3.org/ns/activitystreams#content'][0]['value']?></p>
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
  
  </body>
</html>