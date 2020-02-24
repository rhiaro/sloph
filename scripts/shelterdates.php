<?
session_start();
require_once('../vendor/init.php');

function delete($ep, $uri, $graph){
  $q = query_remove_from_graph($uri, $graph);
  $r = execute_query($ep, $q);
  return $r;
}

function insert($ep, $turtle){
  $q = query_insert($turtle, "https://blog.rhiaro.co.uk/");
  $r = execute_query($ep, $q);
  return $r;
}

if(isset($_POST['save'])){
    $uri = trim($_POST['uri']);
    $rdfphp[$uri] = array();
    if(isset($_POST['startTime'])){
        $startdate = new DateTime($_POST['startTime'], new DateTimeZone($_POST['tz']));
        $startdatetime = $startdate->format(DATE_ATOM);
        $rdfphp[$uri]["https://www.w3.org/ns/activitystreams#startTime"][] = array(
            "type" => "literal",
            "datatype" => "http://www.w3.org/2001/XMLSchema#dateTime",
            "value" => $startdatetime
        );
    }
    if(isset($_POST['endTime'])){
        $enddate = new DateTime($_POST['endTime'], new DateTimeZone($_POST['tz']));
        $enddatetime = $enddate->format(DATE_ATOM);
        $rdfphp[$uri]["https://www.w3.org/ns/activitystreams#endTime"][] = array(
            "type" => "literal",
            "datatype" => "http://www.w3.org/2001/XMLSchema#dateTime",
            "value" => $enddatetime
        );
    }

    $newgraph = new EasyRdf_Graph($uri);
    $newgraph->parse($rdfphp, 'php');
    $turtle = $newgraph->serialise('ntriples');
    $ins = insert($ep, $turtle);
}

$shelter_q = $q = get_prefixes();
$shelter_q .= "
CONSTRUCT { ?s ?p ?o . } WHERE {
    ?s ?p ?o .
    ?s rdf:type asext:Acquire .
    ?s as:tag <https://rhiaro.co.uk/tags/shelter> .
    ?s as:published ?pub .
}
ORDER BY DESC(?pub)
";
$graph = execute_query($ep, $shelter_q);
?>
<!doctype html>
<html>
  <head>
    <title>shelter</title>
    <link rel="stylesheet" href="../views/normalize.min.css" />
    <link rel="stylesheet" href="../views/base.css" />
    <link rel="stylesheet" href="../views/core.css" />
    <style type="text/css">
    input[type=text], textarea { width: 50%; padding: 0.6em; }
    .green { background-color: green; }
    </style>
  </head>
  <body>
    <div class="w1of2" style="position: fixed; top: 1em; right: 1em; border: 1px grey solid">
        <?if(isset($_POST['save'])):?>
            <?=$newgraph->dump()?>
            <pre>
                <?var_dump($ins)?>
            </pre>
        <?endif?>
    </div>
    <div class="w1of2" style="padding: 1em;">
    <?foreach($graph as $uri => $post):?>
      <form id="<?=$uri?>" method="post" action="#<?=$uri?>">
        <?
        $published = get_value(array($uri=>$post), "as:published");
        $publisheddt = new DateTime($published);
        $start = get_value(array($uri=>$post), "as:startTime");
        $end = get_value(array($uri=>$post), "as:endTime");
        $startset = false;
        $endset = false;
        if(isset($start)){ $start = new DateTime($start); $startset = true; }else{ $start = $publisheddt; }
        if(isset($end)){ $end = new DateTime($end); $endset = true; }else{ $end = $publisheddt; }
        ?>
        <input name="uri" type="hidden" value="<?=$uri?>"/>
        <p><strong><?=$uri?></strong></p>
        <p><?=$published?></p>
        <p><?=get_value(array($uri=>$post), "as:content")?> (<?=get_value(array($uri=>$post), "asext:cost")?>)</p>
        <p<?=!$startset ? ' class="green"': ''?>><label>Start: </label><input type="date" name="startTime" value="<?=$start->format("Y-m-d")?>" /></p>
        <p<?=!$endset ? ' class="green"': ''?>><label>End: </label><input type="date" name="endTime" value="<?=$end->format("Y-m-d")?>" /></p>
        <p><input type="text" name="tz" value="<?=$start->format("e")?>" style="width: 6em" /></p>
        <p><input type="submit" value="save" name="save" /></p>
      </form>
    <?endforeach?>
    </div>
    <div class="w1of1" id="table">
        <table>
            <tr>
                <th>Pub</th>
                <th>Start</th>
                <th>End</th>
                <th>Descr</th>
                <th>Country</th>
            </tr>
            <?foreach($graph as $uri => $post):?>
                <tr>
                    <td><?=get_value(array($uri=>$post), "as:published")?></td>
                    <td><?=get_value(array($uri=>$post), "as:startTime")?></td>
                    <td><?=get_value(array($uri=>$post), "as:endTime")?></td>
                    <td><?=get_value(array($uri=>$post), "as:content")?></td>
                    <td></td>
                </tr>
            <?endforeach?>
        </table>
    </div>
  </body>
</html>