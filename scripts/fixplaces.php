<?
session_start();
require_once("../vendor/init.php");

if(isset($_POST["submit"])){
    $ins = get_prefixes();
    $ins .= "
INSERT INTO <https://rhiaro.co.uk/places/> {";
    foreach($_POST["uri"] as $i => $val){
        $ins .= "
    <$val> a as:Place .
    <$val> as:name \"{$_POST['name'][$i]}\" .
    <$val> as:latitude \"{$_POST['lat'][$i]}\" .
    <$val> as:longitude \"{$_POST['lng'][$i]}\" .
    <$val> owl:sameAs <{$_POST['sameas'][$i]}> .

";
    }

}

$q = get_prefixes();
$q .= "
SELECT distinct ?place WHERE {
  ?s a as:Travel .
  { ?s as:target ?place . } union { ?s as:origin ?place . }
}
";
$q2 = get_prefixes();
$q2 .= "
SELECT distinct ?place WHERE
{
  ?place a as:Place .
}";

$q1_uris = select_to_list(execute_query($ep, $q));
$q2_uris = select_to_list(execute_query($ep, $q2));
$place_uris = array_unique(array_merge($q1_uris, $q2_uris));
asort($place_uris);
$places = construct_uris($ep, $place_uris);
$by_sameas = array();
foreach($places as $uri => $place){
    $samesies = get_values(array($uri=>$place), "owl:sameAs");
    if($samesies){
        foreach($samesies as $same){
            $by_sameas[$same][$uri] = $place;
        }
    }
}
?>
<!doctype html>
<html>
    <head>
        <title>Places fixie</title>
    </head>
    <body>
        <?if(isset($_POST["submit"])):?>
            <pre>
                <?=htmlentities($ins)?>
            </pre>
        <?endif?>
        <form method="post">
            <?foreach($place_uris as $uri):?>

                <p>
                    <strong><?=$uri?></strong>
                </p>
                <?if((!isset($places[$uri]) && !isset($by_sameas[$uri])) || (isset($places[$uri]) && !has_type(array($uri=>$places[$uri]), "as:Place"))):?>
                <p>
                    <label>URI</label> <input type="text" name="uri[]" value="https://rhiaro.co.uk/location/" />
                    <label>sameAs</label> <input type="text" name="sameas[]" value="<?=$uri?>" />
                </p>
                <p>
                    <label>Name</label> <input type="text" name="name[]" />
                    <label>Lng</label> <input type="text" name="lng[]" />
                    <label>Lat</label> <input type="text" name="lat[]" />
                </p>
                <?endif?>
                <?
                if(isset($places[$uri])){
                    $g = new EasyRdf_Graph($uri);
                    $g->parse(array($uri=>$places[$uri]));
                    echo $g->dump();
                }
                ?>
            <?endforeach?>
            <input type="submit" name="submit" value="generate query" />
        </form>
    </body>
</html>