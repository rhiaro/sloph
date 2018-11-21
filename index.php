<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$headers = apache_request_headers();
$relUri = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);
$graph = get_container_dynamic($ep, $relUri, "query_select_s_and_type_desc", array(1), $ct);
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

    $now = new DateTime();
    $from = new DateTime($now->format("Y-m-01"));
    $to = new DateTime($now->format("Y-m-t"));
    $month_posts = get_posts($ep, $from->format(DATE_ATOM), $to->format(DATE_ATOM));

    $locations = get_locations($ep);
    if($locations != null){
      $locations = $locations->toRdfPhp();
    }
    $tags = get_tags($ep);  

    $last_checkin = construct_last_of_type($ep, "as:Arrive");
    $checkin_summary = make_checkin_summary($last_checkin, $locations);

    $consume_stats = stat_box($ep, "consume");
    $exercise_stats = stat_box($ep, "exercise");
    $budget_stats = stat_box($ep, "budget", $month_posts);
    $words_stats = stat_box($ep, "words", $month_posts);

    $project_icons = get_project_icons($ep);

    // Massively overshoot on first count to balance out disproportionate number of notes vs articles
    $notes_q = query_select_s_type_sort("as:Note", "as:published", "DESC", 16);
    $articles_q = query_select_s_type_sort("as:Article", "as:published", "DESC", 16);
    $notes_res = execute_query($ep, $notes_q);
    $articles_res = execute_query($ep, $articles_q);

    $posts_res = array();
    $posts_res["variables"] = $articles_res["variables"];
    $posts_res["rows"] = array_merge($articles_res["rows"], $notes_res["rows"]);
    $toomany_post_uris = array_reverse(select_to_list_sorted($posts_res, "sort"));
    $latest_post_uris = array_slice($toomany_post_uris, 0, 9);
    $next = array_pop($latest_post_uris);

    $latest_posts = construct_and_sort($ep, $latest_post_uris, "as:published");

    $contact_q = query_construct("https://rhiaro.co.uk/contact");
    $contact_post = execute_query($ep, $contact_q);

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }
    // Hardcoding some stuff for homepage..
    // TODO: get this from the store
    $resource->addLiteral('view:stylesheet', "views/home.css");
    $colorschemecss = "
    header { 
      background-image: url('https://i.amy.gy/headers/20180929_dahlia.jpg'); 
      background-color: #470229;
    }
    nav {
      border-bottom: 2px solid #470229;
    }
    header h1 {
      color: #470229;
    } 
    nav a {
      color: #470229;
    }
    nav li a:hover {
      color: #fff;
      background-color: #470229;
    }
    footer {
      background-color: #470229;
    }
    ";
    $resource->addLiteral('view:css', $colorschemecss);

    // Don't need this to be an EasyRdf Resource any more
    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';

    ?>
    <header>
      <div class="rhiaro">
        <img src="https://rhiaro.co.uk/stash/dp.png" id="me" />
      </div>
      <div class="projects">
        <h1><span>rhiaro</span></h1>
        <p><span>Timezone: <strong><?=current_timezone($ep);?></strong></span></p>
        <p><span>Currently <strong><a href="<?=$checkin_summary["location_uri"]?>"><?=$checkin_summary["location"]?></a></strong> (for <?=$checkin_summary["for"]?>)</span></p>
        <p><span style="opacity: 0.8">You may know me from..</span></p>
        <?foreach($project_icons as $group):?>
          <div>
            <?foreach($group as $project):?>
              <a href="<?=$project["uri"]?>"><div class="project-box" title="<?=$project["name"]?>" style="background-color: <?=$project["color"]?>"><img src="<?=$project["icon"]?>" alt="" title="<?=$project["name"]?>" /></div></a>
            <?endforeach?>
          </div>
        <?endforeach?>
      </div>
      <div class="stats">
        <p>Last ate <?=time_ago($consume_stats["published"])?> (<a href="<?=$consume_stats["uri"]?>"><?=$consume_stats["content"]?></a>)</p>
        <div class="stat-box"><div style="width: <?=$consume_stats["width"]?>; background-color: <?=$consume_stats["color"]?>"></div></div>
        <p>Last exercised <?=time_ago($exercise_stats["published"])?></p>
        <div class="stat-box"><div style="width: <?=$exercise_stats["width"]?>; background-color: <?=$exercise_stats["color"]?>"></div></div>
        <p>Monthly budget (<a href="<?=$budget_stats["uri"]?>">last spent</a> <?=$budget_stats["cost"]?> on <?=$budget_stats["content"]?>)</p>
        <div class="stat-box"><div style="width: <?=$budget_stats["width"]?>; background-color: <?=$budget_stats["color"]?>"></div></div>
        <p>Words written this month (<?=$words_stats["value"]?> of posts and fiction)</p>
        <div class="stat-box"><div style="width: <?=$words_stats["width"]?>; background-color: <?=$words_stats["color"]?>"></div></div>
      </div>
    </header>

    <nav>
      <ul>
        <li><a href="#latest">Posts</a></li>
        <li><a href="#archive">Archive</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>

    <main class="wrapper w1of1">
      <div id="latest">
        <?foreach($latest_posts as $uri => $data):?>
          <? $resource = array($uri => $data); ?>
          <? include 'views/article.php'; ?>
        <?endforeach?>
        <nav id="nav"><p><a href="<?=$next?>" id="prev" rel="prev">earlier</a></p></nav>
      </div>

      <div id="archive">
        <? include 'views/archive.php'; ?>
      </div>
      <nav><p><a href="#top">top</a></p></nav>

      <div id="contact">
        <?if($contact_post):?>
          <? $resource = $contact_post; ?>
          <? include 'views/article.php'; ?>
        <?endif?>
      </div>

      <nav><p><a href="#top">top</a></p></nav>
    </main>

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