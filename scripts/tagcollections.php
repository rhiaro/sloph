<?
require_once('../vendor/init.php');

function posts_between($ep, $from, $to){
  $q = construct_between($from->format(DATE_ATOM), $to->format(DATE_ATOM));
  // var_dump(htmlentities($q));
  $posts = execute_query($ep, $q);
  return $posts;
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
      if($i < 200){
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

          if($rins){
              $qdel = get_prefixes();
              $qdel .= "DELETE { ?post as:tag \"\"\"$tag\"\"\" . }";
              $rdel = execute_query($ep, $qdel);
              if($rdel){
                echo "<p><strong>Success:</strong> $tag -> $taguri</p>";
              }else{
                echo "<p><strong>Delete failed</strong>: $tag</p>";
              }
          }else{
            break;
            echo "<p><strong>Insert failed</strong>: $taguri</p>";
            var_dump(htmlentities($qins));
          }
      }
        
    }
            
}

function name_tags($ep){

    // prefix as: <https://www.w3.org/ns/activitystreams#> .
    // delete { ?tag as:name ?name . } where { ?tag as:name ?name . FILTER(?name = "") }

    $tagsq = query_select_tags();
    $tagsr = execute_query($ep, $tagsq);
    // var_dump($tagsr);
    foreach($tagsr["rows"] as $tag){
      if($tag["tag type"] == "uri" && (!isset($tag["name"]) || $tag["name"] == "") ){
        $name = str_replace("+", " ", str_replace("https://rhiaro.co.uk/tags/", "", $tag["tag"]));
        $ttl = "<{$tag['tag']}> as:name \"\"\"$name\"\"\" .";
        $ins_q = query_insert($ttl);
        $ins_r = execute_query($ep, $ins_q);
        if($ins_r){
          echo "<p>Success: $name</p>";
        }else{
          var_dump(htmlentities($ins_q));
        }
        echo "<hr/>";
      }
    }
}

function fix_names($ep){
  $names = array(
    "https://rhairo.co.uk/tags/soy" => "https://rhiaro.co.uk/tags/soy",
    "https://rhairo.co.uk/tags/milkshake" => "https://rhiaro.co.uk/tags/milkshake",
    "https://rhiaro.co.uk/tag/star trek" => "https://rhiaro.co.uk/tags/star+trek",
    "https://rhiarol.co.uk/tags/potato" => "https://rhiaro.co.uk/tags/potato",
    "https:///rhiaro.co.uk/tags/fruit" => "https://rhiaro.co.uk/tags/fruit",
    "https://rhiaro.co.uk/tagslinked research" => "https://rhiaro.co.uk/tags/linked+research",
    "https://rhiaro.co.uk/bu mi" => "https://rhiaro.co.uk/tags/bu+mi",
    "https://rhiaro.co.u/tags/travel" => "https://rhiaro.co.uk/tags/travel",
    "/beach" => "https://rhiaro.co.uk/tags/beach",
    "https://rhiaro.co..uk/tags/pancakes" => "https://rhiaro.co.uk/tags/pancakes",
    "http://rhiaro.co.uk/tags/noodles" => "https://rhiaro.co.uk/tags/noodles",
    "https://rhiaro.co.uk/taags/nachos" => "https://rhiaro.co.uk/tags/nachos",
    "https://rhiaro.co.uk/cornish pasty co" => "https://rhiaro.co.uk/tags/cornish+pasty+co",
    "https://rhiaro.col.uk/tags/vegetables" => "https://rhiaro.co.uk/tags/vegetables",
    "https://rhairo.co.uk/tags/publishing" => "https://rhiaro.co.uk/tags/publishing",
    "https://rhiarol.co.uk/tags/zen xin" => "https://rhiaro.co.uk/tags/zen+xin",
    "https://rhiaro.co.uk/justgo" => "https://rhiaro.co.uk/tags/justgo",
    "https://rhiaro.uk/tags/restaurant" => "https://rhiaro.co.uk/tags/restaurant",
    "https://rhiaro.co..uk/tags/annalakshmi" => "https://rhiaro.co.uk/tags/annalakshmi",
    "https://rhiaro.co..uk/tags/bread" => "https://rhiaro.co.uk/tags/bread",
    "https://rhiaro.uk/tags/transit" => "https://rhiaro.co.uk/tags/transit",
    "https://rhairo.co.uk/tags/icoca" => "https://rhiaro.co.uk/tags/icoca",
    "https://rhiaro.col.uk/tags/restaurant" => "https://rhiaro.co.uk/tags/restaurant`",
    "https://rhiaro.co.u/tags/curryco" => "https://rhiaro.co.uk/tags/curryco",
    "https://rhiaro.co.ukk/tags/fruit" => "https://rhiaro.co.uk/tags/fruit",
    "https://rhiaro.co.co.uk/tags/dumplings" => "https://rhiaro.co.uk/tags/dumplings",
    "https://rhiaro.uk/tags/soup" => "https://rhiaro.co.uk/tags/soup",
    "https://rhiaro.co.uk/tag/vegetables" => "https://rhiaro.co.uk/tags/vegetables",
    "https://rhiaro.co.u.k/tags/known" => "https://rhiaro.co.uk/tags/known",
    "https://rhiaro.co.uk/pancakes" => "https://rhiaro.co.uk/tags/pancakes",
    "https://rhiaro.co.uk/ozeki" => "https://rhiaro.co.uk/tags/ozeki",
    "https://rhiaro.co..uk/tags/noodles" => "https://rhiaro.co.uk/tags/noodles",
    "https://rhairo.co.uk/tags/groceries" => "https://rhiaro.co.uk/tags/groceries",
    "http://rhiaro.co.uk/tags/coffee" => "https://rhiaro.co.uk/tags/coffee",
    "https://rhairo.co.uk/tags/baratollo" => "https://rhiaro.co.uk/tags/baratollo",
    "http://rhiaro.co.uk:443/scripts/localedit.php?uri=https://rhiaro.co.uk/2017/07/https;//rhiaro.co.uk/tags/beans" => "https://rhiaro.co.uk/tags/beans",
    "https://rhiaro.co.uk/fruit" => "https://rhiaro.co.uk/tags/fruit",
    "https://rhiarol.co.uk/tags/beach" => "https://rhiaro.co.uk/tags/beach",
    "https://rhiaro.co.uk/oats" => "https://rhiaro.co.uk/tags/oats",
    "https://rhiaro.co.uk/tag/leftovers" => "https://rhiaro.co.uk/tags/leftovers",
    "https://rhairo.co.uk/tags/rice" => "https://rhiaro.co.uk/tags/rice",
    "https://rhiaro.co.uk/tag/laundry" => "https://rhiaro.co.uk/tags/laundry",
    "https://rhairo.co.uk/tags/soya" => "https://rhiaro.co.uk/tags/soy",
    "https://rhiaroco.uk/tags/bread" => "https://rhiaro.co.uk/tags/bread",
    "https://rhiaro.co.uk/tag/restaurant" => "https://rhiaro.co.uk/tags/restaurant",
    "https://rhiaro.co.uk/vegetables" => "https://rhiaro.co.uk/tags/vegetables",
    "https://rhiaro.co.uk/tag/forum" => "https://rhiaro.co.uk/tags/forum",
    "https://rhiaro.co..uk/tags/avocado" => "https://rhiaro.co.uk/tags/avocado",
    "https://rhiaro.co.uk/tagstags/vegan" => "https://rhiaro.co.uk/tags/vegan",
    "https://rhairo.co.uk/tags/bon appetit" => "https://rhiaro.co.uk/tags/bon+appetit",
    "https://rhiaro.co.uk/location/blind tiger" => "https://rhiaro.co.uk/tags/blind+tiger",
    "https://rhiaro.co.uk/location/serenity" => "https://rhiaro.co.uk/tags/serenity",
    "https://rhairo.co.uk/tags/tofu" => "https://rhiaro.co.uk/tags/tofu",
    "https://rhiaro.co..uk/tags/curry" => "https://rhiaro.co.uk/tags/curry",
    "https://rhiaro.co.uk/t/ags/hatha yoga" => "https://rhiaro.co.uk/tags/hatha",
    "https://rhiaro.co.u/tags/curry" => "https://rhiaro.co.uk/tags/curry",
    "https://linkedresearch.org" => "https://linkedresearch.org/"
  );
  foreach($names as $old => $new){
    $uri = str_replace(" ", "+", $old);
    $ins_q = get_prefixes()."
    INSERT INTO <https://blog.rhiaro.co.uk/> { ?post as:tag <$new> . } WHERE { ?post as:tag <$uri> . }";
    $del_q = get_prefixes()."
    DELETE { ?post as:tag <$uri> } WHERE { ?post as:tag <$uri> . }";
    $del_tag = query_delete($uri);

    var_dump(htmlentities($ins_q));
    $ins_r = execute_query($ep, $ins_q);
    if($ins_r){
      echo "$new updated";
      echo "<hr/>";
      var_dump(htmlentities($del_q));
      $del_r = execute_query($ep, $del_q);
      if($del_r){
        echo "$uri deleted";
        echo "<hr/>";
        var_dump(htmlentities($del_tag));
        $tag_r = execute_query($ep, $del_tag);
        if($tag_r){
          echo "Tag deleted";
          echo "<hr/>";
        }
      }
      
    }

  }
}

function do_tags($ep, $posts){

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
          // echo $g->dump();

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

if(isset($_GET['action'])){
  $action = $_GET['action'];
}else{
  $action = "collate";
}

?>

<!doctype html>
<html>
  <head><title>Tag stuff</title></head>
  <body>
    <nav>
      <a href="?action=collate&from=<?=$from->format("Y-m-d")?>&to=<?=$to->format("Y-m-d")?>">Collate</a> | <a href="?action=clean&from=<?=$from->format("Y-m-d")?>&to=<?=$to->format("Y-m-d")?>">Clean</a> | <a href="?action=name">Name</a>
    </nav>
    <h1>Posts between <?=$from->format("Y-m-d")?> and <?=$to->format("Y-m-d")?></h1>
<?
$posts = posts_between($ep, $from, $to);
if($action == "collate"){
  do_tags($ep, $posts);
}
if($action == "clean"){
  $strtags = get_string_tags($ep);
  echo "<p>".count($strtags)." (1768)</p>"; 
  remove_old_tags($ep);
}
if($action == "name"){
  // fix_names($ep);
  name_tags($ep);
}
?>

  </body>
</html>