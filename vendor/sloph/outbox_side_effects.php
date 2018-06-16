<?
require_once("../init.php");

function create_create($ep, $object, $create_date=null){
    global $_RHIARO;
    $create_uri = make_uri($ep, $object);
    $object_uri = get_uri($object);

    if($create_date === null){
        $date = new DateTime('now');
    }else{
        $date = new DateTime($create_date);
    }
    
    $g = new EasyRdf_Graph($create_uri);
    $g->addType('as:Create');
    $g->addResource('as:object', $object_uri);
    $g->addResource('as:actor', $_RHIARO);
    $g->addLiteral('as:published', $date);
    
    // TODO: Copy addressing from object to create.

    $ttl = $g->serialize('turtle');
    $q = query_insert($ttl);
    $r = execute_query($ep, $q);
    return $r;
}

function process_update($ep, $activity){
    
}

function process_delete($ep, $activity){
    
}

function process_undo($ep, $activity){
    
}

function add_to_collection($ep, $post_uri, $collection_uri){
    
}

function update_tags_collection($ep, $post){
    
}

function update_likes_collection($ep, $post){
    
}

function update_liked_collection($ep, $post){
    
}

function update_shares_collection($ep, $post){
    
}

function update_shared_collection($ep, $post){
    
}

function update_followers_collection($ep, $post){
    
}

function update_following_collection($ep, $post){
    
}

function update_blocked_collection($ep, $post){
    
}

function add_published_date($ep, $post, $date){
    
}

function add_modified_date($ep, $post, $date){
    
}

function add_actor($ep, $post, $actor){
    
}

?>