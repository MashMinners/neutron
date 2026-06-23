<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use DateTime;
use InvalidArgumentException;
use SimpleXMLElement;

class BaseInvoiceXmlParser
{
    private $folder = 'storage/cmis/';
    private function getXmlFileName(array $files, string $pattern){
        $filtered = array_filter($files, function($item) use ($pattern){
            return preg_match($pattern, $item);
        });
        return ((string)reset($filtered));
    }
    private function simpleXmlToArray(SimpleXMLElement $xmlObject): array
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
    public function parseXML(array $files) : array{
        $xmlFiles['D'] = $this->getXmlFileName($files, '/^D/');
        $xmlFiles['F'] = $this->getXmlFileName($files, '/^F/');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/^L/');
        foreach ($xmlFiles AS $key => $file){
            $path = $this->folder.$file;
            $xml = simplexml_load_file($path);
            $array[$key] = $this->simpleXmlToArray($xml);
        }
        return $array;
    }

}