<!doctype html>
<html>
  <head>
    <title><?=$resource->get('as:name')?></title>
    <link rel="stylesheet" type="text/css" href="/views/normalize.min.css" />
    <link rel="stylesheet" type="text/css" href="/views/core.css" />
    <link rel="stylesheet" type="text/css" href="/<?=$resource->get('view:stylesheet')?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      <?=$resource->get('view:css')?>
    </style>
  </head>
  <body>