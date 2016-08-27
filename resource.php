<?
session_start();
require_once('vendor/init.php');

if(isset($_GET['resource'])){
  $resource = $_GET['resource'];
  $headers = apache_request_headers();
  $ct = $headers["Accept"];

  $result = get($ep, $resource, $ct);
  $header = $result['header'];
  $content = $result['content'];

}else{
  $message["Beyond the final frontier"] = "You shouldn't be here.";
}

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource();
    // $resource->addLiteral('view:banality', 3);
    // $resource->addLiteral('view:intimacy', 3);
    // $resource->addLiteral('view:tastiness', 3);
    // $resource->addLiteral('view:wanderlust', 3);
    // $resource->addLiteral('view:informative', 3);
    $resource = set_views($ep, $resource);
    include 'views/top.php';

    if($resource->isA("as:Arrive")){
      include 'views/checkin.php';
    }else{
      include 'views/article.php';
    }

    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}

?>