<?
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

$_PREF = array(
         'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'
        ,'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#'
        ,'foaf' =>  'http://xmlns.com/foaf/0.1/'
        ,'dc' => 'http://purl.org/dc/elements/1.1/'
        ,'dct' => 'http://purl.org/dc/terms/'
        ,'sioc' => 'http://rdfs.org/sioc/types#'
        ,'blog' => 'http://vocab.amy.so/blog#'
        ,'as' => 'http://www.w3.org/ns/activitystreams#'
        ,'mf2' => 'http://microformats.org/profile/'
        ,'ldp' => 'http://www.w3.org/ns/ldp#'
        ,'solid' => 'http://www.w3.org/ns/solid#'
      );
$_NS = array_flip($_PREF);
// TODO: sioc is wrong
// TODO: deal with dublin core
foreach($_PREF as $prefix => $uri){
  EasyRdf_Namespace::set($prefix, $uri);
}

include_once("sloph/queries.php");

?>