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

function get_string_tags($ep){
  $q = query_select_tags();
  $res = execute_query($ep, $q);
  // var_dump($res);
  $tags = array(); $i = 0;
  foreach($res['rows'] as $tag){
    if($tag["tag type"] == "literal"){
      $uri = "https://rhiaro.co.uk/tags/".urlencode($tag["tag"]);
      if(isset($tag["name"])){
        $tags[$uri]['name'] = $tag["name"];
      }else{
        $tags[$uri]['name'] = $tag["tag"];
      }
    }
  }
  return $tags;
}

function get_uri_tags($ep){
  $q = query_select_tags();
  $res = execute_query($ep, $q);
  // var_dump($res);
  $tags = array(); $i = 0;
  foreach($res['rows'] as $tag){
    if($tag["tag type"] == "uri"){
      $uri = $tag["tag"];
      if(isset($tag["name"])){
        $tags[$uri]['name'] = $tag["name"];
      }else{
        $tags[$uri]['name'] = urldecode(str_replace("https://rhiaro.co.uk/tags/", "", $tag["tag"]));
      }
    }
  }
  return $tags;
}

function tags_to_collections($ep){
  $tags = get_tags($ep);
  
  foreach($tags as $uri => $tag){
    $q = get_prefixes();
    $q .= "INSERT INTO <https://rhiaro.co.uk/tags/> {
  <$uri> a as:Collection .
  <$uri> as:items ?post .
} WHERE {
  ?post as:tag <$uri> .
}";
    var_dump(htmlentities($q));
    echo "<hr/>";
    $r = execute_query($ep, $q);
    var_dump($r);
    echo "<hr/>";
  }
}

function wipe_tag_collections($ep){
  $tags = get_tags($ep);
  foreach($tags as $uri => $tag){
    $q = get_prefixes();
    $q .= "DELETE { <$uri> as:items ?post }
WHERE { <$uri> as:items ?post }";
    $r = execute_query($ep, $q);
    var_dump($r);
    echo "<hr/>";
  }
}

function move_graph($ep, $from, $to){
  $q = query_select_s(0, $from);
  $r = execute_query($ep, $q);
  $others = array();
  foreach($r["rows"] as $uri){
    if(stripos($uri["s"], "https://rhiaro.co.uk/") === false){
      $others[] = $uri["s"];
    }
  }
  foreach($others as $o){
    $q_ins = get_prefixes();
    $q_ins .= "insert into <$to> 
{ <$o> ?p ?o . }
where { graph <$from> { <$o> ?p ?o . } }";
    echo "<strong>$o</strong><br/>";
    //$r_ins = execute_query($ep, $q_ins);
    echo "<pre>";
    var_dump(htmlentities($q_ins));
    echo "</pre>";
    echo "<br/></br/>";

    $q_del = get_prefixes();
    $q_del .= "delete from <$from> 
{ <$o> ?p ?o . }";
    //$r_del = execute_query($ep, $q_del);
    echo "<pre>";
    var_dump(htmlentities($q_del));
    echo "</pre>";
    echo "<hr/>";
  }

}

function generate_summary($ep, $resource){

  $places = execute_query($ep, query_for_places());

  $as = "http://www.w3.org/ns/activitystreams#";
  $ex = "https://terms.rhiaro.co.uk/as#";
  $s = "Amy";
  $types = get_values($resource, 'rdf:type');
  
  echo current(array_keys($resource))."<br/>";
  var_dump($types); echo "<br/>";

  if(is_array($types)){

    if(in_array($as."Arrive", $types)){
      $place = get_value($resource, 'as:location');
      if(!empty($place) && array_key_exists($place, $places)){
        $where = get_value(array($place => $places[$place]), 'blog:pastLabel');
        if(!empty($where)){
          $s .= " ".$where;
        }else{
          $s = " arrived at ".get_name($place);
        }
      }else{
        $s = false;
      }
    }elseif(in_array($ex."Acquire", $types)){
      $content = get_value($resource, 'as:content');
      $cost = get_value($resource, 'asext:cost');
      $s .= " acquired $content for $cost";
    }elseif(in_array($ex."Consume", $types)){
      $content = get_value($resource, 'as:content');
      $s .= " consumed $content";
    }elseif(in_array($as."Add", $types)){
      $object = get_value($resource, 'as:object');
      $target = get_value($resource, 'as:target');
      $s .= " added $object to $target";
    }elseif(in_array($as."Like", $types)){
      $object = get_value($resource, 'as:object');
      $s .= " liked $object";
    }elseif(in_array($as."Announce", $types)){
      $object = get_value($resource, 'as:object');
      $target = get_value($resource, 'as:target');
      $s .= " shared $object";
      if(!empty($target)){
        $s .= " with $target";
      }
    }elseif(in_array($as."Note", $types)){
      $tags = get_values($resource, 'as:tag');
      if(!empty($tags)){
        $tag_str = array();
        foreach($tags as $turi){
          if(strpos($turi, "http") !== false){
            $tagr = execute_query($ep, query_construct($turi));
            $tagn = get_value($tagr, 'as:name');
            if(!empty($tagn)){
              $tag_str[] = $tagn;
            }
          }
        }
        if(count($tag_str) > 1){
          $tag_str[count($tag_str)-1] = "&amp; ".$tag_str[count($tag_str)-1];
          $tags = implode($tag_str, ", ");
        }else{
          $tags = "something";
        }
      }else{
        $tags = "something";
      }
      $s .= " wrote about $tags";
    
    }elseif(in_array($as."Travel", $types)){
      $origin = get_name($ep, get_value($resource, 'as:origin'));
      $target = get_name($ep, get_value($resource, 'as:target'));

      $s .= " planned a trip from $origin to $target";
    }elseif(in_array($as."Accept", $types)){
      $object = get_value($resource, 'as:object');
      $s .= " RSVP'd to $object";
    }else{
      return false;
    }
  }else{
    return false;
  }

  return $s;
}

function add_summary($ep){

  $q = get_prefixes();

  $res = execute_query($ep, query_select_s());
  $uris = select_to_list($res);
  foreach($uris as $uri){
    if(stripos($uri, "rhiaro.co.uk/") > 0){
      $r = execute_query($ep, query_construct($uri));
      $s = generate_summary($ep, $r);
      if($s){
        $q .= "INSERT INTO <http://blog.rhiaro.co.uk#> { <$uri> as:summary \"$s\" . }";
        echo htmlentities($q);
        $r = execute_query($ep, $q);
        var_dump($r);
      }else{
        echo "skipped";
      }
      echo "<hr/>";
    }
    $q = get_prefixes();
  }
  

}

// transform_mentions($ep);
//transform_content_to_html($ep);
//double_content($ep);
// wipe_tag_collections($ep);
//tags_to_collections($ep); 
// move_graph($ep, "http://blog.rhiaro.co.uk#", "https://rhiaro.co.uk/incoming/");
add_summary($ep);
?>
