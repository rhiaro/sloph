<?
require_once('../vendor/init.php');
$collection = "https://rhiaro.co.uk/tags/socialwg";
if(isset($_GET['before'])){
  $before = $_GET['before'];
}else{
  $before = null;
}
?>
<pre>
<?
var_dump(construct_collection_page($ep, $collection, $before, 16, "as:published"));
?>
</pre>