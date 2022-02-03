<?
require_once('../vendor/init.php');

function move_adds($ep, $from, $post){
  unset($post["submit"]);
  $del = "";
  $ins = "";
  foreach($post as $add => $album){
    $add = str_replace("_", ".", $add);
    if($album != $from){
      $ins .= "<$add> as:target <$album> . ";
      $del .= "<$add> as:target <$from> . ";
    }
  }
  $q = get_prefixes();
  $qins = $q."\nINSERT INTO <https://blog.rhiaro.co.uk/> {\n\t$ins\n}\n";
  $qdel = $q."\nDELETE FROM <https://blog.rhiaro.co.uk/> {\n\t$del\n}\n";
  $res = execute_query($ep, $qins);
  if($res){
    $resdel = execute_query($ep, $qdel);
  }

  return array($res, $resdel);
}

function get_adds($ep, $album){
  // Get add posts by album
  $q = query_select_s_where(array("rdf:type" => "as:Add", "as:target" => "<".$album.">"), 0);
  $res = execute_query($ep, $q);

  // $adds = array();
  // foreach($res['rows'] as $r){
  //   $qa = query_construct($r['s']);
  //   $ra = execute_query($ep, $qa);
  //   $adds[$r['s']] = $ra[$r['s']];
  // }
  // return $adds;
  return select_to_list($res);
}

if(isset($_GET["from"])){
  $from_album = $_GET["from"];
}else{
  $from_album = "https://i.amy.gy/madestuff/";
}

if(isset($_POST['submit'])){
  $moved = move_adds($ep, $from_album, $_POST);
}

$albums = get_albums($ep);
$madestuff = get_adds($ep, $from_album);

?>
<!doctype html>
<html>
<head><title>Move adds from one album to another</title></head>
<body>

<form autocomplete="off">
  <h1>Move from
    <select name="from">
      <?foreach($albums as $album):?>
        <option<?=($album==$from_album) ? " selected" : ""?>><?=$album?></option>
      <?endforeach?>
    </select>
    <input type="submit" name="subfrom" value="Fetch adds" />
  </h1>
</form>

<?if(isset($_POST['submit'])):?>
<pre>
<?var_dump($moved)?>
</pre>
<?endif?>

<form autocomplete="off" method="post">
  <?foreach($madestuff as $add):?>
    <p>
      <strong><a href="<?=$add?>" target="_blank"><?=$add?></a></strong>
      <select name="<?=$add?>">
        <?foreach($albums as $album):?>
          <option value="<?=$album?>"<?=($album==$from_album) ? " selected" : ""?>><?=$album?></option>
        <?endforeach?>
      </select>
    </p>
  <?endforeach?>
  <input type="submit" name="submit" value="Save" />
</form>

</body>
</html>