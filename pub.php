<?
require_once('vendor/init.php');

function verify_token($token){
  $ch = curl_init("https://tokens.indieauth.com/token");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
       "Content-Type: application/x-www-form-urlencoded"
      ,"Authorization: $token"
  ));
  $response = Array();
  parse_str(curl_exec($ch), $response);
  curl_close($ch);
  return $response;
}

function write($data, $date=null, $slug=null){
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  $turtle = json_to_turtle($data);
  
  if(!$slug) $slug = uniqid();
  if(!$date) $date = date("ymd-His");
    
  $log = "apdumps/".$date."_".$slug.".json";
  $h = fopen($log, 'w');
  fwrite($h, $data);
  fclose($h);
  $log2 = "apdumps/".$date."_".$slug.".ttl";
  $h2 = fopen($log2, 'w');
  if(fwrite($h2, $turtle) !== false){
    fclose($h2);
    return $log2;
  }else{
    return false;
  }
}

// AUTH FIRST
// Verify token
$headers = apache_request_headers();
if(isset($headers['Authorization'])) {
  $token = $headers['Authorization'];
  
  /* !!!!! */
  if($token == $EP_KEY){
    $response = array("me"=>"https://rhiaro.co.uk/#me", "scope"=>"post", "issued_by"=>"localhost");
  }elseif($token == $GUEST_KEY){
    $response = array("me"=>"http://csarven.ca/#i", "scope"=>"post", "issued_by"=>"https://rhiaro.co.uk/sloph");
  }else{
  /* !!!!! */  

    $response = verify_token($token);
  }
  $me = @$response['me'];
  $iss = @$response['issued_by'];
  $client = @$response['client_id'];
  $scope = @$response['scope'];

}else{
  header("HTTP/1.1 403 Forbidden");
  echo "403: No authorization header set.";
  echo "\n... TODO: dump publicly accessible posts here";
  exit;
}

if(empty($response)){
  header("HTTP/1.1 401 Unauthorized");
  echo "401: Access token could not be verified.";
  exit;
}elseif((stripos($me, "rhiaro.co.uk") === false && !is_guest($me)) || $scope != "post"){
  header("HTTP/1.1 403 Forbidden");
  var_dump($me);
  var_dump(is_guest($me));
  echo "403: Access token was not valid.";
  echo "\n... TODO: dump publicly accessible posts here";
  exit;
}else{
  // Good to go
  // Do stuff
  $post = file_get_contents('php://input');
  if(empty($post)){
    $post = $_POST;
  }
  
  if(isset($post) && !empty($post)){
  
  // Get content-type header
  if($headers["Content-Type"] == "application/x-www-form-urlencoded"){
    // if it's form-encoded convert to json-ld
    // TODO: actual conversion
    if(!isset($post["@context"])){ $post["@context"] = "https://rhiaro.co.uk/vocab"; }
    if(isset($post['access_token'])) unset($post['access_token']);
  
  }elseif($headers["Content-Type"] == "application/activity+json"){
    // if it's activity+json add default context
    if(!isset($post["@context"])){ $post["@context"] = "https://www.w3.org/ns/activitystreams#"; }
  
  }elseif($headers["Content-Type"] == "application/ld+json"){
    // pass
  }else{
    header("HTTP/1.1 415 Unsupported Media Type");
    echo "415: Unsupported media type";
    exit;
  }
  
  // Find slug
  $slug = null;
  if(isset($post['slug'])){ 
    $slug = urlencode($post['slug']); 
    unset($post['slug']);
  }elseif(isset($headers['Slug'])){
    $slug = urlencode($headers['Slug']); 
  }

  // parse and validate
  $data = json_encode($post);
  if(!$data){
    header("HTTP/1.1 400 Bad Request");
    echo "400: Bad JSON-LD";
    exit;
  }
  $g = new EasyRdf_Graph();
  $g->parse($post, "jsonld");
  $resources = $g->resources();
  $named = new EasyRdf_Graph();
  foreach($resources as $id => $data){
    if($g->resource($id)->isBNode()){
      $new_uri = make_uri($ep, $g->resource($id));
      $s = new EasyRdf_Resource($new_uri);
      $ps = $data->properties();
      foreach($ps as $p){
        $os = $g->all($id, $p);
        foreach($os as $o){
          $named->add($s, $p, $o);
        }
      }
    }
  }
  // TODO: add default data like author etc
  // insert
  if($me != "https://rhiaro.co.uk/#me"){
    $graph = "https://blog.rhiaro.co.uk/guest/";
  }else{
    $graph = "https://blog.rhiaro.co.uk/";
  }
  $ntriples = $named->serialise("ntriples");
  $q = query_insert_n($ntriples, $graph);
  $res = execute_query($ep, $q);
  if(isset($res["t_count"])){
    header("HTTP/1.1 201 Created");
    header("Location: ".$new_uri);
    echo "201 Created: ".$new_uri;
  }

  }else{
    header("HTTP/1.1 400 Bad Request");
    echo "400: Nothing posted";
  }
  
}

?>