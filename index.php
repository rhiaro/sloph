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
    <div class="wrapper w1of1">
      <h1>
        <img src="https://rhiaro.co.uk/stash/dp.png" alt="rhiaro" rel="as:image foaf:depiction foaf:img" />
      </h1>
      <div id="me" class="w1of2 clearfix" resource="#me" rel="foaf:primaryTopic" typeof="as:Person"><div class="inner">
        <div id="latest">
          <?foreach($latest_posts as $resource):?>
            <? include 'views/article.php'; ?>
          <?endforeach?>
          <nav id="nav"><p><a href="<?=$next?>" id="prev" rel="prev">earlier</a></p></nav>
        </div>
        
      </div> </div>

      <div class="boxes w1of2"><div class="inner">
        
        <div id="stats">
          <p>&#128236; IRL I am <span property="as:name foaf:name">Amy</span>. My current timezone is <?=current_timezone($ep)?>. 
          I store code on <a href="https://github.com/rhiaro" rel="me">github</a> and <a href="https://bitbucket.org/rhiaro">bitbucket</a>. 
          On IRC find me as rhiaro on Freenode, imaginarynet and w3.org.
          On twitter I am <a href="https://twitter.com/rhiaro" rel="me">@rhiaro</a>.
          By email I am <a href="mailto:amy@rhiaro.co.uk" rel="me">amy@rhiaro.co.uk</a>.</p>
          <p><a href="https://nanowrimo.org"><img src="https://i.amy.gy/posts/nanowrimo.png" alt="" /></a> <strong>Nanowrimo 2018</strong>: <a href="https://rhiaro.co.uk/birds">Birds</a>
          [ <a href="/tags/nanowrimo"><?=nanowrimo_total($ep, "2018")?></a> / 50,000 words ]
          (day <?=$day?> goal: <?=number_format(1667*$day, 0, ".", ",")?>
          <? $togo = (1667*$day)-str_replace(",","",nanowrimo_total($ep, "2018")); ?>
          <?=$togo > 0 ? ".. ".number_format($togo, 0, ".", ",")." to go" : "\o/"?>)</p>
          <a href="https://rhiaro.co.uk/ldn.php" rel="ldp:inbox"></a>
          <?foreach($last_of_type as $type => $resource):?>
            <? include 'views/profile_post.php';      ?>
          <?endforeach?>
          <p>&#128241; The header image is my current phone background. This one is <a href="https://i.amy.gy/201809-lithuania/IMG_20180929_153115.jpg">a Dahlia in Vilnius Botanical Gardens, September 2018</a>. Since records began:</p>
          <ul>
            <li><a href="https://i.amy.gy/201807-croatia/IMG_20180703_053016.jpg">Sunset from the hill on Iz island, Croatia, July 2018</a>.</li>
            <li><a href="https://i.amy.gy/201709-uk/IMG_20170910_163334.jpg">Chips and Irn Bru on Portobello beach (Edinburgh) in September 2017</a>.</li>
          </ul>
          <p>&#128169; Here are the 1600 last things I posted. Some of them are <em>quite</em> banal:</p>
        </div>

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
        <div class="w1of1">
          <h3>The 64 things I write about most are:</h3>
          <? $i = 0; ?>
          <p class="tags"><?foreach($tags as $uri => $tag):?>
           <?if($i < 64):?>
             <a href="<?=$uri?>"><?=$tag['name']?> (<?=$tag['count']?>)</a>
             <? $i++; ?>
           <?endif?>
          <?endforeach?></p>
          <p class="tags"><strong><a href="/tags">Find posts by tag</a></strong></p>
        </div>
      </div></div>
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