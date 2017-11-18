<article class="h-entry" typeof="as:Article" about="">
  <h1 class="p-name" property="as:name">This URL points to data about:</h1>
  <ul>
  <?foreach($resource as $uri => $data):?>
    <li>
      <a href="<?=$uri?>"><?=get_value(array($uri => $data), "as:name") ? get_value(array($uri=>$data), "as:name") : $uri?></a>
      (<?=explode("#", get_value(array($uri => $data), "rdf:type"))[1]?>)
    </li>
  <?endforeach?>
  </ul>
</article>