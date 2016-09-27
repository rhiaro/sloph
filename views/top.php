<!DOCTYPE html>
<html>
  <head>
    <title><?=get_value($resource, 'as:name')?></title>
    <link rel="stylesheet" type="text/css" href="/views/normalize.min.css" />
    <link rel="stylesheet" type="text/css" href="/views/core.css" />
    <link rel="stylesheet" type="text/css" href="/<?=get_value($resource, 'view:stylesheet')?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
      <?=get_value($resource, 'view:css')?>
    </style>
  </head>
  <body about="https://rhiaro.co.uk/" typeof="foaf:PersonalProfileDocument as:Profile" prefix="as: http://www.w3.org/ns/activitystreams# foaf: http://xmlns.com/foaf/0.1/ ldp: http://www.w3.org/ns/ldp# view: https://terms.rhiaro.co.uk/view# asext: https://terms.rhiaro.co.uk/as#">