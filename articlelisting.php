<?
session_start();
require_once('vendor/init.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$base = "https://rhiaro.co.uk";

$listing_uri = "https://rhiaro.co.uk/articles";
$graph = new EasyRdf_Graph($listing_uri);

$q = query_construct_vars(array("as:name"=>"name", "as:published"=>"pub"), array("rdf:type"=>"as:Article"), 0, "pub");
$res = execute_query($ep, $q);
$graph->parse($res, 'php');

$article_uris = array_keys($res);
foreach($article_uris as $article_uri){
  $graph->addResource($listing_uri, "as:items", $article_uri);
}

$graph->addType($listing_uri, "as:Collection");
$graph->add($listing_uri, "as:name", "Articles index");
$graph->add($listing_uri, "as:summary", "A list of all titled articles on rhiaro.co.uk");

$result = conneg($acceptheaders, $graph);
$header = $result['header'];
$content = $result['content'];

function article_name($uri, $data){
  $name = get_value(array($uri=>$data), "as:name", $uri);
  if(!empty($name)){
    return $name;
  }else{
    return "(untitled)";
  }
}

$monthname = array("01"=>"January", "02"=>"February", "03"=>"March", "04"=>"April", "05"=>"May", "06"=>"June", "07"=>"July", "08"=>"August", "09"=>"September", "10"=>"October", "11"=>"November", "12"=>"January");

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $graph->addLiteral($listing_uri, "view:stylesheet", "views/base.css");
    $resource = $graph->toRdfPhp();

    if(isset($_GET["yfilter"]) && $_GET["yfilter"] != "0"){
      $yinclude = $_GET["yfilter"];
    }
    if(isset($_GET["mfilter"]) && $_GET["mfilter"] != "0"){
      $minclude = $_GET["mfilter"];
    }

    $sorted_by_month = array();
    $m_filter_opts = array();
    foreach($article_uris as $uri){
      $date = date_from_graph($resource, $uri, "as:published");
      $y = $date->format("Y");
      $m = $date->format("m");
      if(!isset($sorted_by_month[$y])){
        $sorted_by_month[$y] = array();
        $m_filter_opts[$y] = array();
      }
      if(!isset($sorted_by_month[$y][$m])){
        $sorted_by_month[$y][$m] = array();
      }
      $sorted_by_month[$y][$m][$uri] = $resource[$uri];
      $m_filter_opts[$y][] = $m;
    }

    // Make the filter form nice
    $y_filter_opts = array_keys($sorted_by_month);
    // Drop filtered out years
    foreach($sorted_by_month as $y => $months){
      if(isset($yinclude)){
        if($y != $yinclude){
          unset($sorted_by_month[$y]);
        }else{
          $m_filter_opts[$y] = array_keys($months);
        }
      }
      // Drop filtered months
      foreach($months as $m => $data){
        if(isset($minclude) && $m != $minclude){
          unset($sorted_by_month[$y][$m]);
        }
      }
      krsort($months);
      krsort($m_filter_opts);
    }

    krsort($sorted_by_month);

    include 'views/top.php';
?>
<article class="h-entry" typeof="as:Article" about="">
  <h1>Articles index</h1>
  <form id="filter">
    <label>Filter</label>
    <select name="yfilter" id="yfilter">
      <option value="0"<?=!isset($yinclude) ? " selected" : ""?>>all years</option>
      <?foreach($y_filter_opts as $y):?>
        <option value="<?=$y?>"<?=(isset($yinclude) && $yinclude==$y) ? " selected": ""?>><?=$y?></option>
      <?endforeach?>
    </select>
    <select name="mfilter" id="mfilter">
      <option value="0"<?=!isset($minclude) ? " selected" : ""?>>all months</option>
      <?if(isset($yinclude)):?>
        <?foreach($m_filter_opts[$yinclude] as $m):?>
          <option value="<?=$m?>"<?=(isset($minclude) && $minclude==$m) ? " selected" : ""?>><?=$monthname[$m]?></option>
        <?endforeach?>
      <?else:?>
        <?foreach($monthname as $m => $name):?>
          <option value="<?=$m?>"<?=(isset($minclude) && $minclude==$m) ? " selected" : ""?>><?=$name?></option>
        <?endforeach?>
      <?endif?>
    </select>
    <input type="submit" value="filter" name="filter" />
  </form>
  <?foreach($sorted_by_month as $year => $months):?>
    <div id="<?=$year?>">
    <h2><?=$year?></h2>
      <?foreach($months as $month => $articles):?>
        <h3><?=$monthname[$month]?></h3>
        <ul id="<?=$month?>">
          <?foreach($articles as $uri => $data):?>
            <li><a href="<?=$uri?>"><?=article_name($uri,$data)?></a></li>
          <?endforeach?>
        </ul>
      <?endforeach?>
    </div>
  <?endforeach?>

</article>
<script>
  var yearSelect = document.getElementById("yfilter");
  var monthSelect = document.getElementById("mfilter");
  var years = document.querySelectorAll("div");
  var sub = document.querySelectorAll("input[type='submit']");
  yearSelect.addEventListener("onChange", function(){

  });
</script>
<?
    include 'views/end.php';
  }
}catch(Exception $e){
  var_dump($e);
}
?>