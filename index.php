<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$result = get_container_dynamic($ep, $relUri, "query_select_s_and_type_desc", array(1600), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($relUri);

    $last_of_derp = array();
    $latest_post_uris = array();
    $latest_posts = array();

    $items = $content->toRdfPhp();
    $items = array_reverse($items);
    $ns = new EasyRdf_Namespace();

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $locations = get_locations($ep);
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
          $color = $locations->get($item[$ns->expand("as:location")][0]['value'], 'view:color');
          $currentlocation = $item[$ns->expand("as:location")][0]['value'];
        }
      }

      $items[$uri]['color'] = $color;

    }
    if($locations){
      $wherestyle = "body, #me a:hover { background-color: ".$locations->get($currentlocation, 'view:color')."}\n";
      if(!$resource->get('view:css')){
        $resource->addLiteral('view:css', $wherestyle);
      }
    }

    include 'views/top.php';
    include 'views/header.php';

    $items = array_reverse($items);
    $latest_post_uris = array_reverse(array_slice($latest_post_uris, count($latest_post_uris)-6, 6));
    $next = array_pop($latest_post_uris);

    foreach($latest_post_uris as $uri){
      $result = get($ep, $uri);
      $content = $result['content'];
      $resource = $content->resource($uri);
      $latest_posts[$uri] = $resource;
    }
    foreach($last_of_derp as $type => $uri){
      $result = get($ep, $uri);
      $content = $result['content'];
      $resource = $content->resource($uri);
      $last_of_type[$type] = $resource;
    }

    ?>

    <div class="boxes">
      <a href="#me"><img src="https://rhiaro.co.uk/stash/dp.png" alt="profile" class="box" /></a>
      <?
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
    
    <div id="me" class="clearfix" resource="#me" typeof="as:Person">
      <h1>
        <img src="https://rhiaro.co.uk/stash/dp.png" alt="rhiaro" rel="as:image" />
         ... tampering ...
      </h1>
      <div class="w1of2">
        <?foreach($latest_posts as $uri => $resource):?>
          <? include 'views/article.php'; ?>
        <?endforeach?>
        <nav><p><a href="<?=$next?>">Next</a></p></nav>
      </div>
      <div class="w1of2">
        <p>IRL I am <span property="as:name">Amy</span></p>
        <p>On twitter I am <a href="https://twitter.com/rhiaro" rel="me">@rhiaro</a></p>
        <p>I store code on <a href="https://github.com/rhiaro" rel="me">github</a> and <a href="https://bitbucket.org/rhiaro">bitbucket</a></p>
        <p>By email I am <a href="mailto:amy@rhiaro.co.uk" rel="me">amy@rhiaro.co.uk</a></p>
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
    <?
    include 'views/end.php';

  }
}catch(Exception $e){
  var_dump($e);
}

?>