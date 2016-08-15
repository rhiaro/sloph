<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$result = get_container_dynamic($ep, $relUri, "query_select_s_desc", array(500), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($relUri);
    $items = $content->all($relUri, 'as:items');

    $full = array();
    $all = array();
    $majortypes = array("as:Article", "as:Note", "as:Add", "as:Like");

    foreach($items as $item){
      $uri = $item->getUri();
      $result = get($ep, $uri);
      $header = $result['header'];
      $content = $result['content'];
      $resource = $content->resource($uri);

      $types = $resource->types();
      if(empty($types) || array_intersect($majortypes, $types)){
        $full[] = $resource;
      }

      if(!isset($currentlocation) && $resource->isA('as:Arrive')){
        $currentlocation = $resource->get('as:location');
      }

      $all[] = $resource;
    }

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $locations = get_locations($ep);
    $wherestyle = "body { background-color: ".$locations->get($currentlocation, 'view:color')."}";
    if(!$resource->get('view:css')){
      $resource->addLiteral('view:css', $wherestyle);
    }

    include 'views/top.php';
    include 'views/header.php';

    foreach($full as $resource){
      include 'views/article.php';
    }

    foreach($all as $resource){
      include 'views/boxes.php';
    }

    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>