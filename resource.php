<?
session_start();
require_once('vendor/init.php');

if($_SERVER['REQUEST_METHOD'] === "GET" || $_SERVER['REQUEST_METHOD'] === "HEAD"){

  if(isset($_GET['resource'])){
    $resource = $_GET['resource'];
    $resource = str_replace(" ", "+", $resource); // pesky url decoding
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
      // $content = $content->resource();

      if(isset($_GET['debug'])){
        echo "<hr/>".$content->dump();
      }
      
      if($content->isA($content->resource(), "as:Arrive")){
        // Temporary for checkins
        $content->addLiteral($content->resource(), 'view:banality', 5);
        $content->addLiteral($content->resource(), 'view:intimacy', 5);
        $content->addLiteral($content->resource(), 'view:wanderlust', 4);
      }

      if($content->isA($content->resource(), "as:Travel")){
        // Temporary for journeys
        $content->addLiteral($content->resource(), 'view:banality', 3);
        $content->addLiteral($content->resource(), 'view:intimacy', 5);
        $content->addLiteral($content->resource(), 'view:wanderlust', 5);
      }

      if($content->isA($content->resource(), "asext:Consume") || $content->isA($content->resource(), "asext:Acquire")){
        // Temporary for food logs
        $content->addLiteral($content->resource(), 'view:banality', 5);
        $content->addLiteral($content->resource(), 'view:intimacy', 3);
        $content->addLiteral($content->resource(), 'view:tastiness', 5);
      }

      $tags = get_tags($ep);

      $resource = set_views($ep, $content->resource());
      $g = $resource->getGraph();
      $resource = $g->toRdfPhp();
      include 'views/top.php';
      include 'views/nav.php';
      include 'views/'.view_router($resource).'.php';
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