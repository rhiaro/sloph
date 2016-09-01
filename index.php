<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$relUri = $_SERVER['REQUEST_URI'];
$ct = $headers["Accept"];
$result = get_container_dynamic($ep, $relUri, "query_select_s_and_type_desc", array(1000), $ct);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{
    $resource = $content->resource($relUri);
    $items = $content->all($relUri, 'as:items');

    $last_of_derp = array();
    $latest_posts = array();
    $all = array();

    foreach($items as $item){

      $uri = $item->getUri();
      $types = $item->types();
      if(empty($types)){
        $types = array("as:Object");
        $item->addResource("rdf:type", "as:Object");
      }
      foreach($types as $type){
        if(!isset($last_of_derp[$type]) && $type != "as:Activity"){
          $result = get($ep, $uri);
          $content = $result['content'];
          $resource = $content->resource($uri);
          $last_of_derp[$type] = $resource;
        }

        if(($type == "as:Article" || $type == "as:Note") && count($latest_posts) <= 9){
          $result = get($ep, $uri);
          $content = $result['content'];
          $resource = $content->resource($uri);
          $latest_posts[] = $resource;
        }
      }

      if($item->isA('as:Arrive')){
        $res = get($ep, $uri);
        $arrive = $res['content']->resource($uri);
        $item->addResource("as:location", $arrive->get("as:location"));
        if(!isset($currentlocation)){
          $currentlocation = $item->get("as:location");
        }
      }

      $all[] = $item;

    }

    /* Views stuff */
    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $locations = get_locations($ep);
    $wherestyle = "body, #me a:hover { background-color: ".$locations->get($currentlocation, 'view:color')."}\n";
    if(!$resource->get('view:css')){
      $resource->addLiteral('view:css', $wherestyle);
    }

    $tags = get_tags($ep);

    include 'views/top.php';
    include 'views/header.php';
    ?>

    <div class="boxes">
      <a href="#me"><img src="https://rhiaro.co.uk/stash/dp.png" alt="profile" class="box" /></a>
      <?
      foreach($all as $resource){
        include 'views/boxes.php';
      }
      ?>
    </div>
    
    <div id="me" class="clearfix" resource="#me" typeof="as:Person">
      <h1>
        <img src="https://rhiaro.co.uk/stash/dp.png" alt="rhiaro" rel="as:image" />
         ... tmi ...
      </h1>
      <div class="w1of2">

        <?for($i=0; $i < 9; $i++){
          $resource = $latest_posts[$i];
          include 'views/article.php'; 
        }
        ?>
        <nav><p><a href="<?=$latest_posts[9]->getUri()?>">Next</a></p></nav>
      </div>
      <div class="w1of2">
        <p>IRL I am <span property="as:name">Amy</span></p>
        <p>On twitter I am <a href="https://twitter.com/rhiaro" rel="me">@rhiaro</a></p>
        <p>On github I am <a href="https://github.com/rhiaro" rel="me">rhiaro</a></p>
        <p>By email I am <a href="mailto:amy@rhiaro.co.uk" rel="me">amy@rhiaro.co.uk</a></p>
      <?
      foreach($last_of_derp as $resource){
        include 'views/profile_post.php';
      }
      ?>
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