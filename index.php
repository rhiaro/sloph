<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$result = get_container_dynamic($ep, "query_select_s_desc", array(500), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($_SERVER['REQUEST_URI']);
    include 'views/top.php';
    var_dump($resource); // HERENOW
    // $items = $resource->all('as:items');
    // var_dump($items);
    //     $result = get($ep, $uri, "text/html");
    //     $content = $result['content'];
    //     $resource = $content->resource();
    //     include 'views/article.php';


    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>