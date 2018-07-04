<?
require_once('../vendor/init.php');
require_once('../vendor/cashcache/cashcache.php');

function posts_between($from, $to){
  // Fetch all acquire posts from triplestore.
  // Replace this with retreiving posts your way.
  global $ep;
  $q = construct_between($from->format(DATE_ATOM), $to->format(DATE_ATOM));
  // var_dump(htmlentities($q));
  $posts = execute_query($ep, $q);
  $out = array();
  if($posts){
    foreach($posts as $uri => $post){
      if(has_type(array($uri=>$post), 'asext:Acquire')){
        $out[$uri] = $post;
      }
    }
  }
  return $out;
}

function get_string_tags($ep){
    $tagsq = query_select_tags();
    $tagsr = execute_query($ep, $tagsq);
    $tags = select_to_list($tagsr);
    $strtags = [];
    foreach($tags as $tag){
        if(strpos($tag, "http") === false){
            $strtags[] = $tag;
        }
    }

    return $strtags;
}

function remove_old_tags($ep){
    // Nuke once and for all the non-uri tags
    // Shouldn't need to run this again..
    $tag_pref = "https://rhiaro.co.uk/tags/";

    $tags = get_string_tags($ep);
    $i = 0;

    foreach($tags as $tag){
      if($i < 100 && $tag != "archive"){ // something weird wrong with 'archive'
          $i++;
          $taguri = str_replace(" ", "+", strtolower($tag));
          $taguri = str_replace("'", "", $taguri);
          $taguri = str_replace("#", "", $taguri);
          $taguri = str_replace("/", "", $taguri);
          $taguri = str_replace(":", "", $taguri);
          $taguri = str_replace("&", "+", $taguri);
          $taguri = $tag_pref.$taguri;
          // echo $taguri;

          $qins = get_prefixes();
          $qins .= "INSERT INTO <https://blog.rhiaro.co.uk/> {
    ?post as:tag <$taguri> .
  } WHERE {
    ?post as:tag \"\"\"$tag\"\"\" .
  }";
          $rins = execute_query($ep, $qins);
          var_dump($rins);

          if($rins){
              $qdel = get_prefixes();
              $qdel .= "DELETE { ?post as:tag \"\"\"$tag\"\"\" . }";
              $rdel = execute_query($ep, $qdel);
              var_dump($rdel);
          }
      }
        
    }
            
}

function do_tags($ep, $posts){

    // Step 1
    // remove_old_tags($ep);
    // $strtags = get_string_tags($ep);
    // echo "<p>".count($strtags)." (1768)</p>";

    // Step 2
    // Construct tag collections
    // var_dump($posts);
    foreach($posts as $uri => $data){
        echo "<p>$uri</p>";
        $q = query_construct_tag_collections($uri);
        var_dump(htmlentities($q));
        $r = execute_query($ep, $q);
        if($r){
          $g = new EasyRdf_Graph();
          $g->parse($r, 'php');
          $ttl = $g->serialise('ntriples');

          $ins_q = query_insert($ttl, "https://rhiaro.co.uk/tags/");
          $ins_r = execute_query($ep, $ins_q);
          if(!$ins_r){
            echo "<br/><strong>Fail:</strong><br/>";
            var_dump(htmlentities($ins_q));
          }
        }   
        echo "<hr/>";
    }
}

if(isset($_GET['from'])){
  $from = $_GET['from'];
}else{
  $from = "1 week ago";
}

if(isset($_GET['to'])){
  $to = $_GET['to'];
}else{
  $to = "now";
}
$from = new DateTime($from);
$to = new DateTime($to);

?>

<!doctype html>
<html>
  <head><title>Tag stuff</title></head>
  <body>
    <h1>Posts between <?=$from->format("Y-m-d")?> and <?=$to->format("Y-m-d")?></h1>
<?
$posts = posts_between($from, $to);
do_tags($ep, $posts);
?>

  </body>
</html>