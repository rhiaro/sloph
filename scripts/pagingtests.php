<?
require_once('../vendor/init.php');
$collection = "https://rhiaro.co.uk/tags/coffee";
?>
<pre>
<?
var_dump(construct_collection_page($ep, $collection, null, 16, "as:published"));
?>
</pre>