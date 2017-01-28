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
    $locations = $locations->toRdfPhp();
    $color = "transparent";
    $tags = get_tags($ep);
    
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
          $currentlocation = get_value(array($uri => $item), $ns->expand("as:location"));
        }
      }

      $items[$uri]['color'] = $color;

    }

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }
    if($locations){
      $wherestyle = "body, #me a:hover { background-color: ".get_value($locations, 'view:color', $currentlocation)."}\n";
      if(!$resource->get('view:css')){
        $resource->addLiteral('view:css', $wherestyle);
      }
    }

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

    ?>

    <div class="boxes">
      <a href="#me"><img src="https://rhiaro.co.uk/stash/dp.png" alt="AG" class="box" /></a>
      <?
      // TODO: switch boxes template with below
      // foreach($all as $resource){
      //   include 'views/boxes.php';
      // }
      ?>
      <?foreach($items as $uri => $item):?>
        <?foreach($item["http://www.w3.org/1999/02/22-rdf-syntax-ns#type"] as $t):?>
          <?if($t['value'] != EasyRdf_Namespace::expand("as:Activity")):?>
            <a href="<?=$uri?>"><div class="box" style="background-color: <?=$item["color"]?>">
              <?=get_icon_from_type(EasyRdf_Namespace::shorten($t['value']), array("as:Arrive"))?>
            </div></a>
          <?endif?>
        <?endforeach?>
      <?endforeach?>
    </div>
    
    <div id="me" class="clearfix" resource="#me" rel="foaf:primaryTopic" typeof="as:Person">
      <h1>
        <img src="https://rhiaro.co.uk/stash/dp.png" alt="rhiaro" rel="as:image foaf:depiction foaf:img" />
         ... tampering ...
      </h1>
      <div class="w1of2" id="latest">
        <?foreach($latest_posts as $resource):?>
          <? include 'views/article.php'; ?>
        <?endforeach?>
        <nav id="prevnav"><p><a href="<?=$next?>" id="prev" rel="prev">Prev</a></p></nav>
      </div>
      <div class="w1of2">
        <p>IRL I am <span property="as:name foaf:name">Amy</span></p>
        <p>On twitter I am <a href="https://twitter.com/rhiaro" rel="me">@rhiaro</a></p>
        <p>I store code on <a href="https://github.com/rhiaro" rel="me">github</a> and <a href="https://bitbucket.org/rhiaro">bitbucket</a></p>
        <p>By email I am <a href="mailto:amy@rhiaro.co.uk" rel="me">amy@rhiaro.co.uk</a></p>
        <a href="https://rhiaro.co.uk/ldn.php" rel="ldp:inbox"></a>
      <?foreach($last_of_type as $type => $resource):?>
        <? include 'views/profile_post.php';      ?>
      <?endforeach?>
      <h3>The 128 things I write about most are:</h3>
      <? $i = 0; ?>
      <p class="tags"><?foreach($tags as $uri => $tag):?>
       <?if($i < 128):?>
         <a href="<?=$uri?>"><?=$tag['name']?> (<?=$tag['count']?>)</a>
         <? $i++; ?>
       <?endif?>
      <?endforeach?></p>
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
              var prevNav = document.getElementById("prevnav");
              var newNav = prevNav.cloneNode(true);
              var res = JSON.parse(httpRequest.responseText);
              prevNav.parentNode.removeChild(prevNav);
              newNav.querySelector("#prev").href = res.prev;
              posts.insertAdjacentHTML('beforeEnd', res.html);
              posts.appendChild(newNav);
              newNav.querySelector("#prev").onclick = function(e) { e.preventDefault(); makeRequest('vendor/sloph/page.php?type=as:Article,as:Note&start='+res.prev); };
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