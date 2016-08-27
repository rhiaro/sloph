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
    if(isset($_GET['debug'])){
      var_dump($resource);
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

    $resource = set_views($ep, $resource);
    include 'views/top.php';
    include 'views/nav.php';

    if($resource->isA("as:Add") || $resource->isA("as:Like") || $resource->isA("as:Announce")){
      include 'views/link.php';
    }elseif($resource->isA("as:Arrive")){
      include 'views/checkin.php';
    }elseif($resource->isA("as:Travel") && $resource->get('as:origin') && $resource->get('as:target')){
      include 'views/travel.php';
    }elseif($resource->isA("asext:Consume") || $resource->isA("asext:Acquire")){
      include 'views/stuff.php';
    }elseif($resource->isA("as:Invite") || $resource->isA("as:Accept") || $resource->isA("as:Event")){
      include 'views/event.php';
    }else{
      include 'views/article.php';
    }

    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}

?>