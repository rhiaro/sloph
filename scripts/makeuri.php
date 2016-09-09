<?
session_start();
require_once('../vendor/init.php');

$d = new DateTime();

if(isset($_POST['sub'])){
  $g = new EasyRdf_Graph();
  $g->parse(json_encode($_POST['data']), "jsonld");
  echo $g->dump();
  $r = $g->resources();
  $resource = array_pop($r);
  var_dump(post($ep, $resource));
}
?>
<form method="post">
  <p><input type="text" name="data[name]" placeholder="name" /></p>
  <p><textarea name="data[summary]">summary</textarea></p>
  <p><textarea name="data[content]">content</textarea></p>
  <input type="hidden" value="http://www.w3.org/ns/activitystreams#" name="data[@context]" />
  <p><input type="text" name="data[published]" value="<?=$d->format(DATE_ATOM)?>" /></p>
  <p><input type="submit" name="sub" value="Submit" /></p>
</form>