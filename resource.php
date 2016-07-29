<?
session_start();
require_once('vendor/init.php');

if(isset($_GET['resource'])){
  $resource = urldecode($_GET['resource']);
  $headers = apache_request_headers();
  $ct = $headers["Accept"];

  $result = get($ep, $resource, $ct);
  header($result['header']);
  echo $result['content'];

}else{
  $message["Beyond the final frontier"] = "You shouldn't be here.";
}

?>