<?
session_start();
require_once('vendor/init.php');
require_once('vendor/sloph/summary.php');

$headers = apache_request_headers();
$ct = $headers["Accept"];
$acceptheaders = new AcceptHeader($ct);

$contact_uri = "https://rhiaro.co.uk/contact";
$contact_q = query_construct($contact_uri);
$contact_post = execute_query($ep, $contact_q);
$graph = new EasyRdf_Graph($contact_uri);
$graph->parse($contact_post, 'php', $contact_uri);

$me = get_resource($ep, "https://rhiaro.co.uk/#me");

$graph->addResource($contact_uri, "as:attributedTo", $me->resource());

$out = merge_graphs(array($graph, $me), $contact_uri);
$result = conneg($acceptheaders, $out);
$header = $result['header'];
$content = $result['content'];

try {
  if(gettype($content) == "string"){
    header($header);
    echo $content;
  }else{

    $resource = $graph->resource($contact_uri);

    if(!$resource->get('view:stylesheet')){
      $resource->addLiteral('view:stylesheet', "views/".get_style($resource).".css");
    }

    $g = $resource->getGraph();
    $resource = $g->toRdfPhp();

    $includes = array('article.php');
    include 'views/page_template.php';

  }
}catch(Exception $e){
  var_dump($e);
}
?>