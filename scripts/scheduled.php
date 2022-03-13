<?
session_start();
require_once('../vendor/init.php');
$n = new DateTime();
$d = '"'.$n->format(DATE_ATOM).'"^^xsd:dateTime';

$q = get_prefixes();
$q .= "SELECT ?s WHERE {
  ?s as:published ?d .
  FILTER(?d > ".$d.") .
}
ORDER BY DESC(?d)";
$r = execute_query($ep, $q);
if($r){
  $uris = select_to_list($r);
}
?>
<!doctype html>
<html>
  <head>
    <title>Local edit</title>
    <style>
      label { width: 32em; display: inline-block; text-align: right; }
      pre { max-height: 32em; overflow: auto; float: left; border: 1px solid silver; }
      input, textarea { max-width: 100%; border: 1px solid silver; padding: 0.4em; }
      textarea { width: 72em; height: 16em; }
      .info { background-color: #abcdef; padding: 0.4em; font-family: sans-serif; }
      hr { border: 2px solid #abcdef; }
    </style>
  </head>
  <body>

    <h1>Scheduled posts</h1>
    <ul>
      <?foreach($uris as $uri):?>
        <li><a href="localedit.php?uri=<?=$uri?>" target="_blank"><?=$uri?></li>
      <?endforeach?>
    </ul>

  </body>
</html>