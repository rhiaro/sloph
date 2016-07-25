<?
session_start();
require_once('vendor/init.php');

if(isset($_GET['resource'])){
  $resource = urldecode($_GET['resource']);

  if(isset($_GET['ct'])){
    $ct = $_GET['ct'];
  }else{
    $ct = "text/html";
  }

  // Conneg
  $result = get($ep, $resource, $ct);
  header($result['header']);
  echo $result['content'];

}else{
  $message["Beyond the final frontier"] = "You shouldn't be here.";
}

?>