<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$contact_uri = "https://rhiaro.co.uk/contact";
$contact_q = query_construct($contact_uri);
$contact_post = execute_query($ep, $contact_q);

$graph = new EasyRdf_Graph($contact_uri);
$graph->parse($contact_post, 'php', $contact_uri);
$resource = $graph->resource($contact_uri);

require_once('vendor/sloph/header_stats.php');

$g = $resource->getGraph();
$resource = $g->toRdfPhp();

include 'views/top.php';
include 'views/header_stats.php';
include 'views/nav_header.php';
?>

<main class="wrapper w1of1">

  <div id="contact">
    <? include 'views/article.php'; ?>
  </div>
  <nav><p><a href="#top">top</a></p></nav>
</main>

<?
include 'views/end.php';
?>