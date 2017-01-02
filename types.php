<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

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
                ,"articles" => "as:Article"
                ,"writes" => "as:Article"
                ,"notes" => "as:Note"
                ,"places" => "as:Place"
                ,"follows" => "as:Follow"
  );

if(!isset($_GET['type']) || !array_key_exists($_GET['type'], $typemap)){
  header("HTTP/1.1 404 Not Found");
  echo $_GET['type']."<br/>";
  echo "HTTP 404 Not Found. How about <a href=\"https://rhiaro.co.uk/writes/\">/writes</a>?";
  die();
}

$locations = get_locations($ep);
$locations = $locations->toRdfPhp();
$tags = get_tags($ep);

if($_GET['type'] == "places"){
  $sort = "as:name";
}else{
  $sort = "as:published";
}

$q = query_construct_type($typemap[$_GET['type']], $sort);
$res = execute_query($ep, $q);

if($res){
  
  // TODO: Temporary until other people stuff isn't in my graph
  foreach($res as $k => $v){
    if(substr($k, 0, 21) != "https://rhiaro.co.uk/"){ unset($res[$k]); }
  }
  $name = ucfirst($_GET['type']);
  
  $g = get_container_dynamic_from_items($ep, $relUri, $name, $res);
  $result = conneg($acceptheaders, $g);
  $content = $result['content'];
  $header = $result['header'];

  try {
    if(gettype($content) == "string"){
      header($header);
      echo $content;
    }else{

      $resource = set_views($ep, $content->resource());
      $collection = $content->toRdfPhp();
      $resource = array($relUri => $collection[$relUri]);

      include 'views/top.php';
      include 'views/nav.php';
      include 'views/collection.php';

      include 'views/end.php';

    }
  }catch(Exception $e){
    var_dump($e);
  }
}

?>