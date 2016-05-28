<?
require_once "lib/storesetup.php";
include_once "lib/Slogd_Tripler.php";

// Rudimentary security
$auth = false;
include_once "lib/secret.php";
if(isset($_POST['secret']) && $_POST['secret'] == $secret){
  $auth = true;
}

// Data
$triples = array();
$rels = array("mention"=>"blog:mentions", "reply"=>"sioc:reply_of", "like"=>"blog:like_of", "bookmark"=>"blog:bookmark_of", "repost"=>"blog:share_of", "tag"=>"blog:tag_of", "invitation"=>"blog:invite_of");

if(isset($_POST) && $auth){
  if(isset($_POST['source'])) { $source = $_POST['source']; } else { $source = null; }
  if(isset($_POST['target'])) { $target = $_POST['target']; } else { $target = null; }
  if(isset($_POST['property'])) { $property = $rels[$_POST['property']]; } else { $property = $rels['mention']; }
  // mf2 json, only sent from webmention.io cronjob
  if(isset($_POST['sourcedata'])) { $sourcedata = $_POST['sourcedata']; } else { $sourcedata = null; }
}

// If target not exists
//   Fail
// else If source not exists (404 or 410)
//   If have had webmention from this source before OR have mentioned this post myself
//     Delete
//     -> Remove $source from local store
//   else
//     Fail
// else If source not contains link to target
//   Fail
//
// else
//  -> Parse source (expect):
// $sourcedata = '{
//      "items": [
//          {
//              "properties": {
//                  "url": "https://twitter.com/rhiaro/status/622797827312328705",
//                  "author": {
//                      "name": "Misfit Wedding",
//                      "url": "https://twitter.com/MisfitWedding",
//                      "photo": "https://twitter.com/MisfitWedding/profile_image?size=original"
//                  },
//                  "name": "likes this.",
//                  "content": "",
//                  "published": null
//              }
//          }
//      ]
//  }';
//  If have had webmention from this source before
//   This is an update: what has changed?
//     Content - edit "$source-author edited their {inferred post type} of/to $target"
//       -> Store updated $source for display
//     {property} - edit or addition of property "$source-author edited/added {property} of their {inferred post type} of/to $target"
//       -> If this is a property I store/display, store update
//     Replies/comments - new reply (receiving salmention) "$reply-author {inferred post type}d $source-author's post that mentions $target"
//       -> Store for future threading display purposes..
//
// else Infer mention type
//   Content - mention "$source-author mentioned $target in a post."
//   like-of - like "$source-author liked $target"
//   repost-of - repost "$source-author reposted $target"
//   bookmark-of - bookmark "$source-author bookmarked $target"
//   category - tag "$source-author tagged $target in a post" / "$source-author tagged a post with $target"
//   in-reply-to - reply "$source-author replied to $target"
//   {other property} - {other} "$source-author {other}-ed $target"
//   -> Store necessary $source contents for display
if(isset($source) && isset($target)){
  $data = json_decode($sourcedata, true);
  $data = $data['items'][0]['properties'];
  var_dump($data);
  echo "\n\n\n";
  if(!isset($data['published'])) {
    $date = date(DATE_ATOM);
  } else {
    $date = date(DATE_ATOM, strtotime($data['published']));
  }

  $triples[] = "<{$target}> sioc:has_reply <{$source}> .";
  $triples[] = "<{$source}> {$property} <{$target}> .";
  if(isset($data['author'])){
    $triples[] = "<{$source}> dc:creator <{$data['author']['url']}> .";
  }
  if(isset($data['content']) && !empty($data['content'])){
    // Should do this with POST for long posts... or just store the first x chars?
    $mdcontent = file_get_contents("http://fuckyeahmarkdown.com/go/?html=".urlencode($data['content']));
    $triples[] = "<{$source}> sioc:content \"\"\"{$mdcontent}\"\"\" .";
  }
  $triples[] = "<{$source}> dct:created \"{$date}\"^^xsd:datetime . ";
  
  // Author triples. Might as well just update this every time..
  if(isset($data['author'])){
    $triples[] = "<{$data['author']['url']}> foaf:name \"{$data['author']['name']}\" . ";
    $triples[] = "<{$data['author']['url']}> foaf:depiction <{$data['author']['photo']}> . ";
    $triples[] = "<{$data['author']['url']}> foaf:homepage <{$data['author']['url']}> . ";
  }
  
  if(isset($data['name'])){
    $triples[] = "<{$source}> dct:title \"{$data['name']}\" . ";
  }
  
  $insert = "PREFIX dct: <http://purl.org/dc/terms/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sioc: <http://rdfs.org/sioc/types#>
PREFIX blog: <http://vocab.amy.so/blog#>
INSERT INTO <http://blog.rhiaro.co.uk#> {";
  foreach($triples as $t){
    $insert .= $t."\n";
  }
  $insert .="}";
}

//   -> Resend webmentions to all links in $target (send salmention)

// If post from the cron job (with sourcedata)
if(isset($insert) && isset($sourcedata) && $auth){
  //insert
  $res = $ep->query($insert);
  if(!$ep->getErrors()){
    header("HTTP/1.1 202 Accepted");
    var_dump($_POST);
    echo "\n\n";
    echo htmlentities($insert);
    /*$h = fopen("wms/wm_".urlencode($source)."_".time().".txt", 'w');
    fwrite($h, $insert);
    fclose($h);*/
  }else{
    header("HTTP/1.1 400 Bad Request");
    var_dump($ep->getErrors());
  }
}else{
  // display page
  $title = " : Webmention";
  include "templates/home_top.php" ;
?>

<div class="w1of1 color3-bg clearfix">
  <div class="w1of5">
    <? include 'templates/h-card.php'; ?>
  </div>
  <div class="w4of5 lighter-bg"><div class="inner">
    <h1>Send a reply</h1>
    <p>If you've written a reply or linked to one of my posts, this form will send a <a href="https://indiewebcamp.com/webmention">webmention</a> to my post, from yours.</p>
    <form method="post">
      <p><label for="source" class="neat-big">Source (your post):</label> <input type="text" id="source" name="source" class="neat-big"/></p>
      <p class="color1">
        <label for="property" class="neat-big">Property (the relation between your post and mine):</label>
        <select id="property" name="property" class="neat-big">
          <option></option>
          <?foreach($rels as $k=>$v):?>
            <option value="<?=$k?>"><?=$k?></option>
          <?endforeach?>
        </select>
      </p>
      <p><label for="target" class="neat-big">Target (my post):</label> <input type="text" id="target" name="target" class="neat-big"/></p>
      <p><label for="sourcedata">Content (parsed microformats):</label></p>
      <p><textarea id="sourcedata" name="sourcedata" class="neat-big"></textarea></p>
      <p><label for="secret">Secret:</label> <input type="password" id="secret" name="secret" class="neat-big" value="<?=isset($_GET['s']) ? $_GET['s'] : ""?>" /></p>
      <p><input type="submit" value="Send" name="send"/></p>
    </form>
    <?if(isset($_POST["send"])):?>
      <?if($auth):?>
      <pre>
      <?
      echo $_POST;
      echo "<hr/>";
      echo htmlentities($insert);
        
      ?>
      </pre>
      <?else:?>
      <p class="fail">You're not authorised to post at the moment.</p>
      <?endif?>
    <?endif?>
  </div></div>
</div>
<?
  include "templates/end.php";
}
?>