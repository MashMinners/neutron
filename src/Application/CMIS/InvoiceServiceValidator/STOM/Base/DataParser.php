<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Base;

use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleXMLElement;

class DataParser
{
    private string $directory = "storage/cmis/intersections/";
    public function parseExcel(){
        // Ищем файлы .ods и .xlsx
        $odsFiles = glob($this->directory . '*.ods');
        $xlsxFiles = glob($this->directory . '*.xlsx');
        // Объединяем массивы
        $files = array_merge($odsFiles, $xlsxFiles);
        $result = [];
        foreach ($files AS $file){
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $startRow = 'A2';
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $rows = $sheet->rangeToArray(
                "$startRow:$highestColumn$highestRow", // Диапазон
                NULL,                          // Значение для пустых ячеек
                TRUE,                          // Вычислять формулы
                TRUE,                          // Форматировать значения (даты, проценты)
                TRUE                           // Использовать индексы строк/столбцов в массиве
            );
            array_shift($rows);
            $result = array_merge($result, $rows);
        }
        return $result;
    }

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
    public function parseXML() : array{
        $files = glob($this->directory . '*.xml');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/\/L/');
        $xmlFiles['P'] = $this->getXmlFileName($files, '/\/P/');
        $xmlFiles['H'] = $this->getXmlFileName($files, '/\/H/');
        foreach ($xmlFiles AS $key => $file){
            $xml = simplexml_load_file($file);
            $array[$key] = $this->simpleXmlToArray($xml);
        }
        return $array;
    }

}