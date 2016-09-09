<?
session_start();
require_once('vendor/init.php');

if($_SERVER['REQUEST_METHOD'] === "GET" || $_SERVER['REQUEST_METHOD'] === "HEAD"){

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
    if(!$content){
      header($header);
      echo $header;
    }elseif(gettype($content) == "string"){
      header($header);
      echo $content;
    }else{

      $resource = $content->resource();
      if(isset($_GET['debug'])){
        echo "<hr/>".$resource->dump();
      }
      
      if($resource->isA("as:Arrive")){
        // Temporary for checkins
        $resource->addLiteral('view:banality', 5);
        $resource->addLiteral('view:intimacy', 5);
        $resource->addLiteral('view:wanderlust', 4);
      }

      if($resource->isA("as:Travel")){
        // Temporary for journeys
        $resource->addLiteral('view:banality', 3);
        $resource->addLiteral('view:intimacy', 5);
        $resource->addLiteral('view:wanderlust', 5);
      }

      if($resource->isA("asext:Consume") || $resource->isA("asext:Acquire")){
        // Temporary for food logs
        $resource->addLiteral('view:banality', 5);
        $resource->addLiteral('view:intimacy', 3);
        $resource->addLiteral('view:tastiness', 5);
      }

      $tags = get_tags($ep);

      $resource = set_views($ep, $resource);
      include 'views/top.php';
      include 'views/nav.php';

      include 'views/'.view_router($resource).'.php';

      if(isset($_GET['debug'])){
        // var_dump($resource);
        echo "<hr/>".$resource->dump();
      }

      include 'views/end.php';
    }
  }catch(Exception $e){
    var_dump($e);
  }

}elseif($_SERVER['REQUEST_METHOD'] === "POST"){
  // Is this a container?
  // Is the body rdf?
  //   post($ep, $body, $thisuri) to create new resource
  // else Is this a resource?
  //   TODO
}elseif($_SERVER['REQUEST_METHOD'] === "DELETE"){
  // TODO
}elseif($_SERVER['REQUEST_METHOD'] === "PUT"){
  // TODO
}elseif($_SERVER['REQUEST_METHOD'] === "OPTIONS"){
  // TODO
}

?>