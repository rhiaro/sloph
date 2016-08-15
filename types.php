<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];

$typemap = array("checkins" => "as:Arrive"
                ,"arrives" => "as:Arrive"
                ,"consumes" => "asext:Consume"
                ,"eats" => "asext:Consume"
                ,"acquires" => "asext:Acquire"
                ,"stuff" => "asext:Acquire"
                ,"likes" => "as:Like"
                ,"events" => "as:Event"
                ,"bookmarks" => "as:Add"
                ,"reposts" => "as:Announce"
                ,"rsvps" => "as:Accept"
  );

$vals = array("rdf:type" => $typemap[$_GET['type']], "as:published" => "?date");
$result = get_container_dynamic($ep, $relUri, "query_select_s_where", array($vals, 0, "date"), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($relUri);
    $items = $content->all($relUri, 'as:items');

    if(!$resource->get('view:css')){
      $resource->addLiteral('view:css', 'views/'.get_style($resource).".css");
    }

    include 'views/top.php';
    include 'views/header.php';

    foreach($items as $item){
      $uri = $item->getUri();
      $result = get($ep, $uri);
      $header = $result['header'];
      $content = $result['content'];
      $resource = $content->resource($uri);

      include 'views/article.php';
    }

    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>