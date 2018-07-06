<?
session_start();
require_once('vendor/init.php');

$tags = get_tags($ep);

$uri = "https://rhiaro.co.uk/tags/";
$g = new EasyRdf_Graph($uri);
$g->add($uri, 'rdf:type', 'as:Collection');
$g->add($uri, 'as:name', count($tags)." tags");
$resource = $g->toRdfPhp();

include 'views/top.php';

?>
<article>
	<h1><?=count($tags)?> tags</h1>
	<p>
		<input type="text" placeholder="Search" name="tagsearch" id="tagsearch" />
	</p>
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