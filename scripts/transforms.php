<head>
<style>pre { max-height: 16em; max-width: 60%; border: 1px solid silver; overflow: auto; padding: 0.6em; }</style>
</head>
<?
session_start();
require_once('../vendor/init.php');
require_once('Parsedown.php');

function transform_mentions($ep){
  $q = get_prefixes();
  $q .= "SELECT ?sub ?pub WHERE {
  ?sub blog:mentions ?obj . ?sub as:published ?pub .
}";
  $r = execute_query($ep, $q);
  if($r){
    foreach($r['rows'] as $res){
      $ins = get_prefixes();
      $pub = new DateTime($res["pub"]);
      $uri = "https://rhiaro.co.uk/".$pub->format("Y/m")."/".uniqid();
      $ins .= "INSERT INTO <http://blog.rhiaro.co.uk#> {
  <$uri> a as:Relationship .
  <$uri> as:subject <".$res["sub"]."> .
  <$uri> as:object ?obj .
  <$uri> as:published ?pub .
  <$uri> as:relationship as:href .
} WHERE {
  <".$res["sub"]."> blog:mentions ?obj .
  <".$res["sub"]."> as:published ?pub . 
}";
      $insr = execute_query($ep, $ins);
      echo $uri."<br/>";
      var_dump($insr);
      echo "<hr/>";
    }
  }
}

function transform_content_to_html($ep){
    $Parsedown = new Parsedown();
    $q = get_prefixes();
    $q .= "SELECT ?s ?content WHERE {
  ?s as:content ?content .
}
ORDER BY ?s
LIMIT 1600";
    $r = execute_query($ep, $q);
    if($r){
      foreach($r['rows'] as $res){
        $html = $Parsedown->text($res['content']);
        if($html != $res['content']){
          $ins = get_prefixes();
          $ins .= "INSERT INTO <http://blog.rhiaro.co.uk#> {
  <".$res['s']."> as:content \"\"\"".addslashes($html)."\"\"\" . 
  }";
          $del = get_prefixes();
          $del .= "DELETE {
  <".$res['s']."> as:content \"\"\"".$res['content']."\"\"\" . 
  }";
          $insr = execute_query($ep, $ins);
          if($insr){
            $delr = execute_query($ep, $del);
            if($delr){
              echo "<p><strong>success ".$res['s']."</strong></p>";
            }else{
              echo "<p>delete failed for ".$res['s']."</p>";
            }
          }else{
            echo "<p>insert failed for ".$res['s']."</p>";
            echo "<pre>".htmlentities($ins)."</pre>";
          }
        }

      }
    }
}

function double_content($ep){
  $q = get_prefixes();
  $q .= "SELECT ?s ?con1 ?con2 WHERE {
  ?s as:content ?con1 .
  ?s as:content ?con2 .
  filter(?con1 != ?con2) .
}";
  $r = execute_query($ep, $q);
  if($r){
    foreach($r['rows'] as $res){
      echo "<p><strong>".$res['s']."</strong></p>";
      echo "<pre>".htmlentities($res['con1'])."</pre>";
      echo "<pre>".htmlentities($res['con2'])."</pre>";
    }
  }
}

function tags_to_collections($ep){
  $tags = get_tags($ep);
  foreach($tags as $uri => $tag){
    $q = get_prefixes();
    $q .= "INSERT INTO <https://rhiaro.co.uk/tags/> {
  <$uri> a as:Collection .
  <$uri> as:name \"{$tag['name']}\" .
  ?post as:tag <$uri> .
  <$uri> as:items ?post .
} WHERE {
  ?post as:tag \"{$tag['name']}\" .
}";
    var_dump(htmlentities($q));
    echo "<hr/>";
    $r = execute_query($ep, $q);
    var_dump($r);
    echo "<hr/>";
  }
}

// transform_mentions($ep);
//transform_content_to_html($ep);
//double_content($ep);
//tags_to_collections($ep);
?>
