<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Class to serialise an EasyRdf_Graph to ActivityStreams 2.0 JSON-LD
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2016 Amy Guy
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_ActivityStreams extends EasyRdf_Serialiser
{
    public function __construct()
    {
        if (!class_exists('\ML\JsonLD\JsonLD')) {
            throw new LogicException('Please install "ml/json-ld" dependency to use JSON-LD serialisation');
        }

        parent::__construct();
    }

    /**
     * @param EasyRdf_Graph $graph
     * @param string        $format
     * @param array         $options
     * @throws EasyRdf_Exception
     * @return string
     */
    public function serialise($graph, $format, array $options = array())
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'jsonld' && $format != 'as2' && $format != 'json') {
            throw new EasyRdf_Exception(__CLASS__.' does not support: '.$format);
        }

        $ld_graph = new \ML\JsonLD\Graph();
        $nodes = array(); // cache for id-to-node association

        foreach ($graph->toRdfPhp() as $resource => $properties) {
   
            if (array_key_exists($resource, $nodes)) {
                $node = $nodes[$resource];
            } else {
                $node = $ld_graph->createNode($resource);
                $nodes[$resource] = $node;
            }

            foreach ($properties as $property => $values) {
                foreach ($values as $value) {
                    if ($value['type'] == 'bnode' or $value['type'] == 'uri') {
                        if (array_key_exists($value['value'], $nodes)) {
                            $_value = $nodes[$value['value']];
                        } else {
                            $_value = $ld_graph->createNode($value['value']);
                            $nodes[$value['value']] = $_value;
                        }
                    } elseif ($value['type'] == 'literal') {
                        if (isset($value['lang'])) {
                            $_value = new \ML\JsonLD\LanguageTaggedString($value['value'], $value['lang']);
                        } elseif (isset($value['datatype'])) {
                            $_value = new \ML\JsonLD\TypedValue($value['value'], $value['datatype']);
                        } else {
                            $_value = $value['value'];
                        }
                    } else {
                        throw new EasyRdf_Exception(
                            "Unable to serialise object to AS2 JSON-LD: ".$value['type']
                        );
                    }

                    if ($property == "http://www.w3.org/1999/02/22-rdf-syntax-ns#type") {
                        $node->addType($_value);
                    } else {
                        $node->addPropertyValue($property, $_value);
                    }
                }
            }
        }

        // OPTIONS
        $use_native_types = !(isset($options['expand_native_types']) and $options['expand_native_types'] == true);
        
        // expanded form
        $data = $ld_graph->toJsonLd($use_native_types);

        // compact
        $compact_context = "https://www.w3.org/ns/activitystreams#";
        $compact_options = array(
            'useNativeTypes' => $use_native_types,
            'compactArrays' => true,
            'optimize' => false
        );

        $data = \ML\JsonLD\JsonLD::compact($data, $compact_context, $compact_options);
        $data->{'@context'} = "https://www.w3.org/ns/activitystreams#";

        if(isset($data->{'@graph'})){
            $nested = $this->nest_graph($data);
        }else{
            $nested = $data;
        }

        return \ML\JsonLD\JsonLD::toString($nested);
    }

    private function nest_graph($data){

        $update = $data;
        $graph = $data->{'@graph'};

        if(count($graph) > 1){

            // Get each subject in the graph
            $ids = array();
            foreach($graph as $i => $object){
                $ids[$i] = $object->id;
            }
            // This is where they are in the graph
            $pointers = array_flip($ids);

            // Thoughts:
            // is the requested URI a subject in the graph? If so, maybe only look for nesting locations here?
            
            // @graph is an array of other graphs (php objects)
            foreach($graph as $k => $s){
                // Each subject graph has predicates and objects
                foreach($s as $p => $o){

                    // id is actually the subject, we only want objects as nesting locations
                    if($p != 'id'){

                        $nest = $this->has_nest($o, $ids);

                        if($nest){
                            $replacements = array();
                            foreach($nest as $id){
                                // Replace this o with whole thing
                                $whole = $graph[$pointers[$id]];
                                $replacements[$id] = $whole;
                                // Remove whole thing from @graph
                                unset($update->{'@graph'}[$pointers[$id]]);
                            }
                            $replacement = $this->replace_object($o, $replacements);
                            $update->{'@graph'}[$k]->{$p} = $replacement;
                        }

                    }
                }
            }
            // Things that could not be nested remain
            if(isset($update->{'@graph'})){
                if(count($update->{'@graph'}) > 1){
                    // IGNORE AND JUST TAKE FIRST FOR NOW.. TODO
                    $final = $update->{'@graph'}[0];
                    unset($update->{'@graph'});
                    foreach($update as $j => $v){
                        $final->{$j} = $v;
                    }
                // Everything is done!
                }else{
                    // Move everything down a level from @graph
                    $final = current($update->{'@graph'});
                    unset($update->{'@graph'});
                    foreach($update as $j => $v){
                        $final->{$j} = $v;
                    }
                }
            }
            return $final;

        }else{
            // TODO: Check there's an AS2 type
            return $data;
        }
        
    }

    private function has_nest($object, $ids){
        /* Looks for graph subjects present as an object.
        /* If the object is a php object, value could be in @id
        /* If the object is a list, could be in a flat list of URIs, or a list of php objects with @id */
        /* Whatever it is, if it's the subject of the @graph (in $ids) return its id. */

        $matches = array();

        if(!is_array($object)){
            $object = array($object);
        }

        foreach($object as $o){
            if(is_object($object)){
                // Should already have been through the AS2 @context so not checking for @id
                if(isset($o->id) && in_array($o->id, $ids)){
                    $matches[] = $o->id;
                }
            }else{
                if(in_array($o, $ids)){
                    $matches[] = $o;
                }
            }
        }
        if(!empty($matches)){
            return $matches;
        }else{
            return false;
        }
    }

    private function replace_object($object, $replacement){
        if(is_array($object)){
            foreach($object as $k => $o){
                if( 
                    (isset($replacement[$o]) && $o == $replacement[$o]->id) || 
                    (is_object($o) && isset($o->id) 
                        && $o->id == $replacement[$o]->id) ){
                    $object[$k] = $replacement[$o];
                }
            }
        }else{
            $replacement = current($replacement);
            if( ($object == $replacement->id) || (is_object($object) && isset($object->id) && $object->id == $replacement->id) ){
                $object = $replacement;
            }
        }

        return $object;
    }
}
