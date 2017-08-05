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
        $data->{'@context'} = "https://www.w3.org/ns/activitystreams#"; // Not sure about this, could screw up with other vocabs..

        $nested = $this->nest_graph($data);

        return \ML\JsonLD\JsonLD::toString($nested);
    }

    private function nest_graph($data){

        $update = $data;

        $graph = $data->{'@graph'};
        if(count($graph) > 1){
            $ids = array();
            foreach($graph as $i => $object){
                $ids[$i] = $object->id;
            }
            $pointers = array_flip($ids);
            foreach($graph as $k => $s){
                foreach($s as $p => $o){
                    // Do not nest id. Maybe should actually have a whitelist?
                    if($p != 'id'){
                        if(!is_array($o)){
                            if(in_array($o, $ids)){
                                $thing = $graph[$pointers[$o]];
                                // Replace $o with $thing
                            }
                        }else{
                            $overlaps = array_intersect($o, $ids);
                            if(count($overlaps) >= 1){
                                foreach($o as $j => $one){
                                    $thing = $graph[$pointers[$one]];
                                    // Replace each $o with $thing
                                    $update->{'@graph'}[$k]->{$p}[$j] = $thing;
                                    // Remove $thing
                                    unset($update->{'@graph'}[$pointers[$one]]);
                                    // Graph should no longer be multiple
                                    // Move everything down a level from graph
                                }
                            }
                        }
                    }
                }
            }
            return $update;

        }else{
            // TODO: Check there's an AS2 type
            return $data;
        }
        
    }
}
