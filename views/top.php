<?
$theme = get_main_theme($ep);
$styles = get_values($resource, 'view:stylesheet', $content->getUri());
$outbox = get_values("https://rhiaro.co.uk/#me", "as:outbox");
$title = get_value($resource, 'as:name', $content->getUri());
if(!$title){
  $title = get_value($resource, 'as:summary', $content->getUri());
}
if(!$title){
  $title = "a post by rhiaro";
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?=$title?></title>
    <link rel="stylesheet" type="text/css" href="/views/normalize.min.css" />
    <link rel="stylesheet" type="text/css" href="/views/core.css" />
    <link href="https://toot.cat/@rhiaro" rel="me"/>
    <link href="https://twitter.com/rhiaro" rel="me"/>
    <link href="https://github.com/rhiaro" rel="me"/>

  <?if(isset($outbox)):?>
    <link about="https://rhiaro.co.uk/#me" rel="as:outbox" href="<?=$outbox?>" />
  <?endif?>

  <?if(isset($external_styles) && is_array($external_styles)):?>
    <?foreach($external_styles as $style):?>
      <link rel="stylesheet" type="text/css" href="<?=$style?>" />
    <?endforeach?>
  <?endif?>

  <?if(isset($styles) && is_array($styles)):?>
    <?foreach($styles as $style):?>
      <link rel="stylesheet" type="text/css" href="/<?=$style?>" />
    <?endforeach?>
  <?endif?>

    <style>

      header { background-color: <?=$theme["color"]?>; background-image: url('<?=$theme["image"]?>'); }
      header h1 { color: <?=$theme["color"]?>; }
      nav { border-bottom: 2px solid <?=$theme["color"]?>; }
      header a, nav a { color: <?=$theme["color"]?>; }
      nav a:hover { color: #fff; background-color: <?=$theme["color"]?> !important; }
      .btn a { background-color: <?=$theme["color"]?>; }
      .btn a:hover { color: <?=$theme["color"]?>; }
      footer { background-color: <?=$theme["color"]?>; }

      <?=get_value($resource, 'view:css')?>
    </style>
  </head>
  <body about="" typeof="as:Document" prefix="as: https://www.w3.org/ns/activitystreams# foaf: http://xmlns.com/foaf/0.1/ ldp: http://www.w3.org/ns/ldp# view: https://terms.rhiaro.co.uk/view# asext: https://terms.rhiaro.co.uk/as# pimspace: http://www.w3.org/ns/pim/space# cc: http://creativecommons.org/ns# sioc: http://rdfs.org/sioc/ns# dbr: http://dbpedia.org/resource/ dbp: http://dbpedia.org/property/ cito: http://purl.org/spar/cito/ oa: http://www.w3.org/ns/oa# schema: http://schema.org/">