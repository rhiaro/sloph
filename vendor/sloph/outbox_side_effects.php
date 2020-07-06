<?

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

function update_tags_collection($ep, $post_graph){
    // Get all tags from post
    $tag_uris = array();
    $tags_graph = new EasyRdf_Graph();
    foreach($post_graph->resources() as $id => $data){
        $tags = $post_graph->all($id, "as:tag");
        foreach($tags as $tag){
            $tag_uris[$tag->getUri()][] = $id;
        }
    }

    // name tags that aren't named
    $q = get_prefixes();
    $q .= "SELECT ?tag WHERE { ";
    $i = 1;
    foreach($tag_uris as $tag => $tagged){
        $q .= "  { <$tag> as:name ?name .  ?tag as:name ?name . }";
        if($i < count($tag_uris)){
          $q .= "  UNION ";
        }
        $i++;
    }
    $q .= "}";
    $res = execute_query($ep, $q);
    if($res){
        $named_tags = select_to_list($res);
        foreach($tag_uris as $tag => $tagged){
            if(!in_array($tag, $named_tags)){
                $name = str_replace("+", " ", str_replace("https://rhiaro.co.uk/tags/", "", $tag));
                $tags_graph->addLiteral($tag, "as:name", $name);
            }
        }
    }

    // add post to tag collection
    foreach($tag_uris as $tag => $tagged){
        $tags_graph->addType($tag, "as:Collection");
        foreach($tagged as $post){
            $tags_graph->addResource($tag, "as:items", $post);
        }
    }
    $ntriples = $tags_graph->serialise("ntriples");
    $q = query_insert_n($ntriples, "https://rhiaro.co.uk/tags/");
    $res = execute_query($ep, $q);
    return $res;
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