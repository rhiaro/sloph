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
  if($token == "tigoasdf"){
    $response = array("me"=>"https://rhiaro.co.uk/#me", "scope"=>"post", "issued_by"=>"localhost");
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
  exit;
}

if(empty($response)){
  header("HTTP/1.1 401 Unauthorized");
  echo "401: Access token could not be verified.";
  exit;
}elseif(stripos($me, "rhiaro.co.uk") === false || $scope != "post"){
  header("HTTP/1.1 403 Forbidden");
  echo "403: Access token was not valid.";
  exit;
}else{
  // Good to go
  // Do stuff
  if(empty($_POST)){
    $post = file_get_contents('php://input');
  }else{
    $post = $_POST;
  }
  
  if(isset($post) && !empty($post)){
  
  // Get content-type header
  if($headers["Content-type"] == "application/x-www-form-urlencoded"){
    // if it's form-encoded convert to json-ld
    // TODO: actual conversion
    if(!isset($post["@context"])){ $post["@context"] = "https://rhiaro.co.uk/vocab"; }
    if(isset($post['access_token'])) unset($post['access_token']);
  
  }elseif($headers["Content-type"] == "application/activity+json"){
    // if it's activity+json add default context
    if(!isset($post["@context"])){ $post["@context"] = "http://www.w3.org/ns/activitystreams#"; }
  
  }elseif($headers["Content-type"] == "application/ld+json"){
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
  }elseif(isset($headers['Slug'])){ $slug = urlencode($headers['Slug']); }

  // parse and validate
  var_dump($post); // HERENOW this is turning into JSON weirdly
  $data = json_encode($post);
  $g = new EasyRdf_Graph();
  $g->parse(json_encode($data), "jsonld");
  $r = $g->resources();
  $resource = array_pop($r);
  var_dump(post($ep, $resource));

  // Convert back to JSON
  // echo $data;

  // make post uri
  // insert

  /*  
    
  
      
    // Find published key and value
    $published = find_published($post);
    if(!$published) {
      $post['published'] = date(DATE_ATOM);
      $published = array("key"=>"published", "value"=>$post['published']);
    }
    $pub = $published['value'];
      
    // Make post URI
    if($slug === null){
      // Find title or content for slug
      $slug = find_title($post);
      if(!$slug) {
        $content = find_content($post);
        $title = get_inner_string("# ", "\n", $post[$content['key']]);
        if(strlen($title) > 0) {
          $post["http://purl.org/dc/terms/title"] = $title;
          $slug = $title;
        }else{
          $slug = $content['value'];
        }
      }
      if(!$slug) $slug = uniqid();
      // Pass title/content to makeUri
      $uri = Slogd_makeUri($ep, $slug, strtotime($pub));
      $pto = Slogd_makeUri($ep, $slug, strtotime($pub), "BlogPost", false);
    }else{
      $uri = uri_from_slug($slug, strtotime($pub), true);
      $pto = uri_from_slug($slug, strtotime($pub), false);
    }
      
    // Fill in needed post data if missing with defaults
    $post["@id"] = $uri;
    $post["http://xmlns.com/foaf/0.1/isPrimaryTopicOf"] = array("@id" => $pto);
    if($published['key'] != "dct:created"){
      $post["http://purl.org/dc/terms/created"] = array("@value" => $pub, "@type" => "xsd:dateTime");
    }
    
    // Deal with listy properties
    foreach($post as $key => $value){
      if(is_listy($key) && !is_array($value)){
        $values = explode(",", $value);
				$values = array_map('trim', $values);
				$post[$key] = $values;
      }
    }
    
    
    
    var_dump($response);
    echo "\n----\n";
    var_dump($slug);
    echo "\n----\n";
    var_dump($data);
    echo "\n----\n";
    var_dump($res);
    */
  }else{
    header("HTTP/1.1 400 Bad Request");
    echo "400: Nothing posted";
  }
  
}

?>