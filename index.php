<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$result = get_container_dynamic($ep, $relUri, "query_select_s_and_type_desc", array(1000), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($relUri);
    $items = $content->all($relUri, 'as:items');

    $last_of_each = array();
    $all = array();

    foreach($items as $item){

      $uri = $item->getUri();
      $types = $item->types();
      if(empty($types)){
        $types = array("as:Object");
        $item->addResource("rdf:type", "as:Object");
      }
      foreach($types as $type){
        if(!isset($last_of_each[$type]) && $type != "as:Activity"){
          $result = get($ep, $uri);
          $content = $result['content'];
          $resource = $content->resource($uri);
          $last_of_each[$type] = $resource;
          
        }
      }

      if($item->isA('as:Arrive')){
        $res = get($ep, $uri);
        $arrive = $res['content']->resource($uri);
        $item->addResource("as:location", $arrive->get("as:location"));
        if(!isset($currentlocation)){
          $currentlocation = $item->get("as:location");
        }
      }

      $all[] = $item;

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

    foreach($last_of_each as $resource){
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