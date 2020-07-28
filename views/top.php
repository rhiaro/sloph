<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?=get_value($resource, 'as:name', $content->getUri())?></title>
    <link rel="stylesheet" type="text/css" href="/views/normalize.min.css" />
    <link rel="stylesheet" type="text/css" href="/views/core.css" />
    <? $outbox = get_values("https://rhiaro.co.uk/#me", "as:outbox"); ?>
    <?if(isset($outbox)):?>
      <link about="https://rhiaro.co.uk/#me" rel="as:outbox" href="<?=$outbox?>" />
    <?endif?>
    <? $styles = get_values($resource, 'view:stylesheet', $content->getUri()); ?>
  <?if(isset($styles) && is_array($styles)):?>
    <?foreach($styles as $style):?>
      <link rel="stylesheet" type="text/css" href="/<?=$style?>" />
    <?endforeach?>
  <?endif?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
      <?=get_value($resource, 'view:css')?>
    </style>
  </head>
  <body about="" typeof="as:Document" prefix="as: https://www.w3.org/ns/activitystreams# foaf: http://xmlns.com/foaf/0.1/ ldp: http://www.w3.org/ns/ldp# view: https://terms.rhiaro.co.uk/view# asext: https://terms.rhiaro.co.uk/as# pimspace: http://www.w3.org/ns/pim/space# cc: http://creativecommons.org/ns# sioc: http://rdfs.org/sioc/ns# dbr: http://dbpedia.org/resource/ dbp: http://dbpedia.org/property/ cito: http://purl.org/spar/cito/ oa: http://www.w3.org/ns/oa# schema: http://schema.org/">