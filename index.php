<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);
$graph = get_container_dynamic($ep, $relUri, "query_select_s_and_type_desc", array(1600), $ct);
$me = get_resource($ep, "https://rhiaro.co.uk/#me");
$out = merge_graphs(array($graph, $me), $relUri);
$result = conneg($acceptheaders, $out);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $me = $content->resource("https://rhiaro.co.uk/#me");
    $me = $me->toRdfPhp();

    $resource = $content->resource($relUri);

    $last_of_derp = array();
    $latest_post_uris = array();
    $latest_posts = array();

    $items = $content->toRdfPhp();
    $items = array_reverse($items);

    $locations = get_locations($ep);
    if($locations != null){
      $locations = $locations->toRdfPhp();
    }
    $color = "transparent";
    $tags = get_tags($ep);

    $now = new DateTime();
    $day = $now->format("j");
    
    foreach($items as $uri => $item){

      $types = $item[$ns->expand("rdf:type")];

      foreach($types as $t){
        $type = $t['value'];
        if($type != $ns->expand("as:Activity") && $type != $ns->expand("as:Collection") && $type != EasyRdf_Namespace::expand("ldp:Container")){
          $last_of_derp[$ns->shorten($type)] = $uri;
        }

        if($type == $ns->expand("as:Article") || $type == $ns->expand("as:Note")){
          $latest_post_uris[] = $uri;
        }

        if($type == $ns->expand("as:Arrive")){
          $color = get_value($locations, 'view:color', get_value(array($uri => $item), "as:location"));
        }
      }

      $items[$uri]['color'] = $color;

    }

    $lastcheckin = construct_uris($ep, select_to_list(execute_query($ep, query_select_one_of_type("as:Arrive"))));
    $currentlocation = get_value($lastcheckin, $ns->expand("as:location"));

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }
    // if($locations){
    //   $wherestyle = "body, #me a:hover { background-color: ".get_value($locations, 'view:color', $currentlocation)."}\n";
    //   if(!$resource->get('view:css')){
    //     $resource->addLiteral('view:css', $wherestyle);
    //   }
    // }

    // Don't need this to be an EasyRdf Resource any more
    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';
    include 'views/header.php';

    $items = array_reverse($items);
    $latest_post_uris = array_reverse(array_slice($latest_post_uris, count($latest_post_uris)-6, 6));
    $next = array_pop($latest_post_uris);

    foreach($latest_post_uris as $uri){
      $result = get($ep, $uri);
      $content = $result['content'];
      $resource = $content->toRdfPhp();
      $latest_posts[] = $resource;
    }
    foreach($last_of_derp as $type => $uri){
      $result = get($ep, $uri);
      $content = $result['content'];
      $resource = $content->toRdfPhp();
      $last_of_type[$type] = array($uri => array_shift($resource));
    }

    $exercise_stats = stat_box($ep, "exercise");

    // var_dump($last_of_type);
    ?>
    <div class="header">
      <div class="projects">
        <p>You may know me from</p>
        <p>a b c d e f g h i j</p>
        <p>Currently ...</p>
        <p>Timezone: <?=current_timezone($ep);?></p>
      </div>
      <div class="rhiaro">
        <img src="https://rhiaro.co.uk/stash/dp.png" />
      </div>
      <div class="stats">
        <p>Last ate <?=time_ago(get_value($last_of_type['asext:Consume'], 'as:published'))?></p>
        <div class="stat-box"><div style="width: <?=stat_box($ep, "consume")["width"]?>; background-color: <?=stat_box($ep, "consume")["color"]?>"></div></div>
        <p>Last exercised <?=$exercise_stats["value"]?></p>
        <div class="stat-box"><div style="width: <?=$exercise_stats["width"]?>; background-color: <?=$exercise_stats["color"]?>"></div></div>
        <p>Monthly budget</p>
        <div class="stat-box"><div style="width: <?=stat_box($ep, "budget")["width"]?>; background-color: <?=stat_box($ep, "budget")["color"]?>"></div></div>
        <p>Words written this week</p>
        <div class="stat-box"><div style="width: <?=stat_box($ep, "words")["width"]?>; background-color: <?=stat_box($ep, "words")["color"]?>"></div></div>
      </div>
    </div>

    <script>

      (function() {
        var httpRequest;
        var posts = document.getElementById("latest");
        var prevLink = document.getElementById("prev");
        var prevUri = prevLink.href;
        prevLink.onclick = function(e) { e.preventDefault(); makeRequest('vendor/sloph/page.php?type=as:Article,as:Note&start='+prevUri); };

        function makeRequest(url) {
          httpRequest = new XMLHttpRequest();

          if (!httpRequest) {
            console.log('Giving up :( Cannot create an XMLHTTP instance');
            return false;
          }
          httpRequest.onreadystatechange = alertContents;
          httpRequest.open('GET', url);
          httpRequest.setRequestHeader('Accept', 'text/html');
          httpRequest.send();
        }

        function alertContents() {
          if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === 200) {
              var nav = document.getElementById("nav");
              var res = httpRequest.responseText;
              nav.parentNode.removeChild(nav);
              posts.insertAdjacentHTML('beforeEnd', res);
              var nextnav = posts.querySelector("#next");
              nextnav.onclick = function(e) { 
                e.preventDefault(); 
                makeRequest('vendor/sloph/page.php?type=as:Article,as:Note&start='+nextnav.href); 
              };
            } else {
              console.log('There was a problem with the request.');
            }
          }
        }
      })();

    </script>
    <?
    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>