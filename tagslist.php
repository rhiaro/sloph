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

$includes = array('listing_tags.php');
$scripts = ["/views/tagsearch.js"];
include 'views/page_template.php';
?>