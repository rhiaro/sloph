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

function add_to_collection($ep, $post_graph){
    // TODO make this make the graph not do any actual inserts
    $itemsq = query_insert_items($collection_uri, $item_uris);
    $itemsr = execute_query($eq, $itemsq);
    if($itemsr){
        $dater = add_modified_date($ep, $collection_uri, $date);
        return $dater;
    }
    return $itemsr;
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
    if(!empty($tag_uris)){
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
    }else{
        return true;
    }
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
    $q = query_insert_lit($post, "as:published", $date, "xsd:dateTime");
    $r = execute_query($ep, $q);
    return $r;
}

function add_modified_date($ep, $post, $date){
    $q = query_insert_lit($post, "as:updated", $date, "xsd:dateTime");
    $r = execute_query($ep, $q);
    return $r;
}

function add_actor($ep, $post, $actor){

}

/*******************************************************/
/* Stuff that's more specific to sloph than general AP */
/*******************************************************/

/* Currency conversions for Acquire posts */
function acquire_currency_conversion($post_graph){
    $acquires = [];
    foreach($post_graph->resources() as $id => $data){
        if($data->isA("asext:Acquire")){
            $acquires[] = $data->getUri();
        }
    }
    if(empty($acquires)){
        return $post_graph;
    }
    foreach($acquires as $uri){
        $cost = structure_cost($post_graph->get($uri, "asext:cost")->getValue());
        $amount = $cost["value"];
        $currency = $cost["currency"];

        if($amount == "0"){
          $usd = $eur = $gbp = 0;
        }else{
            $date = $post_graph->get($uri, "as:published")->getValue();
            $datef = $date->format("Y-m-d");

            $eur = $post_graph->get($uri, "asext:amountEur");
            if(isset($eur)) { $eur = $eur->getValue(); }
            $usd = $post_graph->get($uri, "asext:amountUsd");
            if(isset($usd)) { $usd = $usd->getValue(); }
            $gbp = $post_graph->get($uri, "asext:amountGbp");
            if(isset($gbp)) { $gbp = $gbp->getValue(); }

            // Get conversion rates
            $existing = read_rates($date);

            // Check if EUR already posted
            if(!isset($eur) && $currency == "EUR"){
                $eur = $amount;
            }
            if(!isset($eur)){
                // Get conversion rates for EUR
                // Fetch and store exchange rate if not already
                if(!isset($existing["EUR"][$currency])){
                    $rates = get_fixer_rates($date, $currency);
                    write_rates($date, $rates);
                }
                // Convert to EUR
                $eur = convert_any_to_eur($amount, $currency, $date);
            }

            // Check if USD already posted
            if(!isset($usd) && $currency == "USD"){
                $usd = $amount;
            }
            // If EUR was set we can try to get USD from EUR
            if(isset($eur) && !isset($usd)){
                $usd = convert_eur_to_any($eur, "USD", $date);
            }
            if(!isset($usd)){
                // Get conversion rates for USD
                // Fetch and store exchange rate if not already
                if(!isset($existing["USD"][$currency])){
                    $usdrates = get_currencylayer_rates($date, $currency);
                    write_rates($date, $usdrates, "USD");
                }
                // Convert to USD
                $usd = convert_any_to_usd($amount, $currency, $date);
            }

            // If USD got set but EUR didn't, try to get EUR from USD
            if(isset($usd) && !isset($eur)){
                $eur = convert_any_to_eur($usd, "USD", $date);
            }

            // Check if GBP already posted
            if(!isset($gbp) && $currency == "GBP"){
                $gbp = $amount;
            }
            // If EUR was set we can try to get GBP from EUR
            if(isset($eur) && !isset($gbp)){
                $gbp = convert_eur_to_any($eur, "GBP", $date);
            }
            // If USD was set we can try to get GBP from USD
            if(isset($usd) && !isset($gbp)){
                $gbp = convert_eur_to_any($usd, "GBP", $date);
            }

        }
        if(isset($eur)){
            $post_graph->add($uri, "asext:amountEur", $eur);
        }
        if(isset($usd)){
            $post_graph->add($uri, "asext:amountUsd", $usd);
        }
        if(isset($gbp)){
            $post_graph->add($uri, "asext:amountGbp", $gbp);
        }
    }
    return $post_graph;
}

?>