<?php

namespace Application\XmlParser;

use SimpleXMLElement;

class XmlParser
{
    protected function getXmlFileName(array $files, string $pattern){
        $filtered = array_filter($files, function($item) use ($pattern){
            return preg_match($pattern, $item);
        });
        return ((string)reset($filtered)).'.xml';
    }
    protected function simpleXmlToArray(SimpleXMLElement $xmlObject): array
    {
        $array = [];
        foreach ($xmlObject->children() as $node) {
            $nodeName = $node->getName();
            $attributes = [];
            // Extract attributes if they exist
            if ($node->attributes()) {
                foreach ($node->attributes() as $attrName => $attrValue) {
                    $attributes[$attrName] = (string)$attrValue;
                }
            }
            // If the node has children, recursively convert them
            if ($node->children()->count() > 0) {
                $data = array_merge($attributes, $this->simpleXmlToArray($node));

                // Handle multiple elements with the same name
                if (isset($array[$nodeName])) {
                    if (!is_array($array[$nodeName]) || !isset($array[$nodeName][0])) {
                        $entry = $array[$nodeName];
                        $array[$nodeName] = [];
                        $array[$nodeName][] = $entry;
                    }
                    $array[$nodeName][] = $data;
                } else {
                    //Здесь убрать скобки и тогда USL и STOM будут пополнятся сразу значениями а не массивами
                    $array[$nodeName][] = $data;
                }
            } else {
                // If no children, store the node's value (and attributes if any)
                if (!empty($attributes)) {
                    $array[$nodeName] = array_merge($attributes, ['value' => (string)$node]);
                } else {
                    $array[$nodeName] = (string)$node;
                }
            }
        }
        return $array;
    }

}