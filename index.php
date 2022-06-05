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

$now = new DateTime();

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $me = $content->resource("https://rhiaro.co.uk/#me");
    $me = $me->toRdfPhp();

    $resource = $content->resource($relUri);

    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    require_once('vendor/sloph/header_stats.php');

    // Massively overshoot on first count to balance out disproportionate number of notes vs articles vs adds
    $notes_q = query_select_s_type_sort("as:Note", "as:published", "DESC", 16);
    $articles_q = query_select_s_type_sort("as:Article", "as:published", "DESC", 16);
    $adds_q = query_select_s_type_sort("as:Add", "as:published", "DESC", 16);
    $notes_res = execute_query($ep, $notes_q);
    $articles_res = execute_query($ep, $articles_q);
    $adds_res = execute_query($ep, $adds_q);

    $posts_res = array();
    $posts_res["variables"] = $articles_res["variables"];
    $posts_res["rows"] = array_merge($articles_res["rows"], $notes_res["rows"], $adds_res["rows"]);
    $toomany_post_uris = array_reverse(select_to_list_sorted($posts_res, "sort"));
    $latest_post_uris = array_slice($toomany_post_uris, 0, 9);
    $next = array_pop($latest_post_uris);

    $latest_posts = construct_and_sort($ep, $latest_post_uris, "as:published");

    $in_feed = true;

    // Don't need this to be an EasyRdf Resource any more
    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    include 'views/top.php';
    include 'views/nav_header.php';
    include 'views/header_stats.php';
  ?>

    <main>
      <div id="latest">
        <?foreach($latest_posts as $uri => $data):?>
          <? $resource = array($uri => $data); ?>
          <? include 'views/'.view_router($resource).'.php'; ?>
        <?endforeach?>
        <nav id="nav"><p><a href="<?=$next?>" id="prev" rel="prev">earlier</a></p></nav>
      </div>
    </main>

    <script>

      (function() {
        var httpRequest;
        var posts = document.getElementById("latest");
        var prevLink = document.getElementById("prev");
        var prevUri = prevLink.href;
        prevLink.onclick = function(e) { e.preventDefault(); makeRequest('vendor/sloph/page.php?type=as:Article,as:Note,as:Add&start='+prevUri); };

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
                makeRequest('vendor/sloph/page.php?type=as:Article,as:Note,as:Add&start='+nextnav.href); 
              };
            } else {
              console.log('There was a problem with the request.');
            }
          }
        }
      })();

      var proxyUrl ='<?=$_IMG?>';
    </script>
    <?
    $scripts = array("views/images.js");
    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>