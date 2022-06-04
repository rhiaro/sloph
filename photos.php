<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$base = "https://rhiaro.co.uk";

if(isset($_GET['album'])){
  $archive_uri = $_GET['album'];
  $album_q = query_construct($archive_uri);
  $album_data = execute_query($ep, $album_q);
}else{
  $archive_uri = "https://rhiaro.co.uk/photos";
  $album_data = null;
}

$graph = new EasyRdf_Graph($archive_uri);
$graph->addLiteral($archive_uri, "view:stylesheet", "views/base.css");
$graph->addLiteral($archive_uri, "view:stylesheet", "views/photos.css");

if($album_data){
  
  $graph->parse($album_data, 'php');

  $adds_q = query_construct_adds($archive_uri);
  $adds = execute_query($ep, $adds_q);
  $total = 0;
  foreach($adds as $add_uri => $add_data){
    // $graph->addResource($archive_uri, "as:items", $add_uri);
    $photos = get_values(array($add_uri => $add_data), "as:object");
    $total = $total + count($photos);
    foreach($photos as $photo){
      $graph->addResource($archive_uri, "as:items", $photo);
    }
  }
  $graph->addLiteral($archive_uri, "as:totalItems", $total);
  $graph->parse($adds, 'php');

}else{

  $graph->addType($archive_uri, "as:Collection");
  $graph->add($archive_uri, "as:name", "Photos archive");
  $graph->add($archive_uri, "as:summary", "Contains links to photo albums.");

  $q = query_construct_albums();
  $res = execute_query($ep, $q);
  $graph->parse($res, 'php');

  $albums = array_keys($res);
  foreach($albums as $album){
    $coverimage = get_value(array($album => $res[$album]), "as:image");
    if(!$coverimage){
      $imgq = query_select_image($album);
      $imgr = execute_query($ep, $imgq);
      $img = select_to_list($imgr);
      $img = $img[0];
      $graph->addResource($album, "as:image", $img);
    }

    $totalitems = get_value(array($album => $res[$album]), "as:totalItems");
    if(!$totalitems){
      $qcount = query_count_added_items($album);
      $rcount = execute_query($ep, $qcount);
      $totalitems = $rcount["rows"][0]["c"];
      $graph->addLiteral($album, "as:totalItems", $totalitems);
    }
  }

}
  $result = conneg($acceptheaders, $graph);
  $header = $result['header'];
  $content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $resource = $graph->resource($archive_uri);

    require_once('vendor/sloph/header_stats.php');

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';
    include 'views/nav_header.php';
    include 'views/header_stats.php';

?>

    <main class="wrapper w1of1">

      <div id="photos">
        <?
        if(!isset($album_data)){
          include 'views/photos.php';
        }else{
          include 'views/album.php';
        }
        ?>
      </div>
      <nav><p><a href="#top">top</a></p></nav>
    </main>
    <script>
      var proxyUrl ='<?=$_IMG?>';
    </script>
    <?
    $scripts = array("/views/images.js");
    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>