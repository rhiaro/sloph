<?
session_start();
require_once('vendor/init.php');

$tags = get_tags($ep);

if(isset($_GET['q']) && $_GET['q'] != ""){
	$filtered = array();
	foreach($tags as $uri => $tag){
		if(strpos(strtolower($tag['name']), strtolower($_GET['q'])) > -1){
			$filtered[$uri] = $tag;
		}
	}
	$tags = $filtered;
}

$uri = "https://rhiaro.co.uk/tags/";
$content = new EasyRdf_Graph($uri);
$content->add($uri, 'rdf:type', 'as:Collection');
$content->add($uri, 'as:name', count($tags)." tags");
$content->add($uri, 'view:stylesheet', 'views/base.css');
$content->add($uri, 'view:stylesheet', 'views/search.css');
$resource = $content->toRdfPhp();

include 'views/top.php';

?>
<article>
	<h1><?=count($tags)?> tags</h1>
	<form>
		<p>
			<input type="text" placeholder="Search" name="q" id="tagsearch" /> 
			<input type="submit" value="Search" id="searchsubmit" />
		</p>
	</form>
	<ul class="tags" id="tagslist">
	<?foreach($tags as $uri => $tag):?>
		<li><a href="<?=$uri?>"><?=$tag['name']?> (<?=$tag['count']?>)</a></li>
	<?endforeach?>
	</ul>
</article>
<?
$scripts = ["/views/tagsearch.js"];
include 'views/end.php';
?>