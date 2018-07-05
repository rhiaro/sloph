<?
// TODO: There must be a less insane way to include these
require_once("JsonLD/Exception/JsonLdException.php");
require_once("JsonLD/Quad.php");
require_once("JsonLD/Processor.php");
require_once("JsonLD/FileGetContentsLoader.php");
require_once("JsonLD/RemoteDocument.php");
require_once("JsonLD/JsonLD.php");
require_once("JsonLD/JsonLdSerializable.php");
require_once("JsonLD/GraphInterface.php");
require_once("JsonLD/IRI.php");
require_once("JsonLD/NodeInterface.php");
require_once("JsonLD/Node.php");
require_once("JsonLD/Graph.php");
require_once("JsonLD/RdfConstants.php");
require_once("JsonLD/Value.php");
require_once("JsonLD/LanguageTaggedString.php");
require_once("JsonLD/TypedValue.php");

require_once("easyrdf/easyrdf/lib/EasyRdf.php");
include_once("ARC2/ARC2.php");
include_once("dbsettings.php");

/* MySQL and endpoint configuration */ 
$config = array(
  /* db */
  'db_host' => $DB_HOST, 
  'db_name' => $DB_NAME,
  'db_user' => $DB_USER,
  'db_pwd' => $DB_PW,

  /* store name */
  'store_name' => 'blog_store',

  /* endpoint */
  'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe', 
    'load', 'insert', 'delete', 
    'dump' /* dump is a special command for streaming SPOG export */
  ),
  'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
  'endpoint_read_key' => '', /* optional */
  'endpoint_write_key' => $EP_KEY, /* optional, but without one, everyone can write! */
);

/* instantiation */
$ep = ARC2::getStoreEndpoint($config);

if (!$ep->isSetUp()) {
  $ep->setUp(); /* create MySQL tables */
}

$_RHIARO = "https://rhiaro.co.uk/#me";

$_PREF = array(
         'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'
        ,'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#'
        ,'foaf' =>  'http://xmlns.com/foaf/0.1/'
        ,'dc' => 'http://purl.org/dc/elements/1.1/'
        ,'dct' => 'http://purl.org/dc/terms/'
        ,'sioc' => 'http://rdfs.org/sioc/types#'
        ,'blog' => 'http://vocab.amy.so/blog#'
        ,'as' => 'https://www.w3.org/ns/activitystreams#'
        ,'mf2' => 'http://microformats.org/profile/'
        ,'ldp' => 'http://www.w3.org/ns/ldp#'
        ,'solid' => 'http://www.w3.org/ns/solid#'
        ,'view' => 'https://terms.rhiaro.co.uk/view#'
        ,'asext' => 'https://terms.rhiaro.co.uk/as#'
        ,'dbp' => 'http://dbpedia.org/property/'
        ,'geo' => 'http://www.w3.org/2003/01/geo/wgs84_pos#'
        ,'doap' => 'http://usefulinc.com/ns/doap#'
        ,'time' => 'http://www.w3.org/2006/time#'
      );
$_NS = array_flip($_PREF);
$ns = new EasyRdf_Namespace();
// TODO: sioc is wrong
// TODO: deal with dublin core
foreach($_PREF as $prefix => $uri){
  EasyRdf_Namespace::set($prefix, $uri);
}
$_CONTEXT = json_encode($_PREF);

/* ActivityStreams 2.0 handling (needs work probably) */
require_once('easyrdf/easyrdf/lib/EasyRdf/Serialiser/ActivityStreams.php');
EasyRdf_Format::register(
    'as2',
    'ActivityStreams 2.0',
    'https://www.w3.org/TR/activitystreams-core/',
    array(
        'application/activity+json' => 1.0,
        'application/ld+json' => 0.9,
        'application/json' => 0.8
    ),
    array('jsonld', 'json')
);
EasyRdf_Format::registerSerialiser('as2', 'EasyRdf_Serialiser_ActivityStreams');

/* Substitute for EasyRdf objects because for some reason they're slow.
   This expects an EasyRdf PHP serialisation.
*/
function get_values($graph, $p, $s=null){
  global $ns;
  if(!isset($s)){
    $s = get_uri($graph);
  }
  $vs = array();
  if(isset($graph[$s][$ns->expand($p)])){
    foreach($graph[$s][$ns->expand($p)] as $v){
      $vs[] = $v['value'];
    }
    return $vs;
  }else{
    return null;
  }
}
function get_value($graph, $p, $s=null){
  $vs = get_values($graph, $p, $s);
  if(is_array($vs)) { return $vs[0]; }
  else return $vs;
}
function has_type($graph, $type, $s=null){
  global $ns;
  $vs = get_values($graph, "rdf:type", $s);
  $type = $ns->expand($type);
  if(is_array($vs) && in_array($type, $vs)){
    return true;
  }
  return false;
}
function get_uri($graph){
  if(!is_array($graph)){
    return $graph;
  }
  return array_keys($graph)[0];
}

function merge_graphs($array_of_graphs, $graph_uri = null){
  $phps = array();
  foreach($array_of_graphs as $g){
    $php = $g->toRdfPhp();
    $phps = array_merge_recursive($phps, $php);
  }
  $graph = new EasyRdf_Graph($graph_uri);
  $graph->parse($phps, 'php');
  return $graph;
}

function get_subject_uris($graph){
  $subjects = array();
  $ar = $graph->toRdfPhp();
  foreach($ar as $s => $po){
    $subjects[] = $s;
  }
  return $subjects;
}

function get_subject_resources($graph){
  $uris = get_subject_uris($graph);
  $ar = $graph->toRdfPhp();
  $out = new EasyRdf_Graph($graph->getUri());
  foreach($uris as $s){
    $r = $graph->resource($s);
    $out->parse(array($s => $ar[$s]), 'php', $s);
  }
  return $out;
}

require_once("AcceptHeader.php");
require_once("sloph/views.php");
require_once("sloph/queries.php");
require_once("sloph/acl.php");
require_once("sloph/ldp.php");
require_once("sloph/outbox_side_effects.php");

date_default_timezone_set(current_timezone($ep));

function current_timezone($ep){
  $q = query_select_timezone();
  $r = execute_query($ep, $q);
  if(count($r['rows']) < 1){
    return 'Europe/Sarajevo';
  }else{
    return $r['rows'][0]['tz'];
  }
}
?>