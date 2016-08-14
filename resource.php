<?
session_start();
require_once('vendor/init.php');

if(isset($_GET['resource'])){
  $resource = urldecode($_GET['resource']);
  $headers = apache_request_headers();
  $ct = $headers["Accept"];

  $result = get($ep, $resource, $ct);
  $header = $result['header'];
  $content = $result['content'];

}else{
  $message["Beyond the final frontier"] = "You shouldn't be here.";
}

$resource = $content->resource();
// $resource->addLiteral('view:banality', 3);
// $resource->addLiteral('view:intimacy', 3);
// $resource->addLiteral('view:tastiness', 3);
// $resource->addLiteral('view:wanderlust', 3);
// $resource->addLiteral('view:informative', 3);
if(!$resource->get('view:css')){
  $resource->addLiteral('view:css', 'views/'.get_style($resource).".css");
}
include 'views/top.php';
?>
  <article>
    <h1><?=$resource->get('as:name')?></h1>
    <?=$resource->get('as:content')?>
    <p><em><?=$resource->type()?></em></p>

  </article>
<?
include 'views/end.php';
?>

