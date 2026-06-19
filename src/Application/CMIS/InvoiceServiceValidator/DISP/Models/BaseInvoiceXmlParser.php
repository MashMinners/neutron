<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use DateTime;
use InvalidArgumentException;
use SimpleXMLElement;

class BaseInvoiceXmlParser
{
    private $folder = 'storage/cmis/';
    protected function getXmlFileName(array $files, string $pattern){
        $filtered = array_filter($files, function($item) use ($pattern){
            return preg_match($pattern, $item);
        });
        return ((string)reset($filtered));
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
    protected function parseD(string $file){
        $path = $this->folder.$file;
        $xml = simplexml_load_file($path);
        $array = $this->simpleXmlToArray($xml);
        foreach ($array['ZAP'] AS $key => $value){
            $idPac =$value['PACIENT'][0]['ID_PAC'];
            foreach ($value['Z_SL'][0]['SL'][0]['USL'] AS $key => $value){
                $data[$idPac][] = $value['CODE_USL'];
            }
        }
        return $data;
    }

    protected function parseF(string $file){

    }

    protected function getAgeInCurrentYear($birthDate){
        $birth = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$birth) {
            throw new InvalidArgumentException('Неверный формат даты. Используйте ГГГГ-ММ-ДД');
        }

        // Текущая дата
        $now = new DateTime();

        // Вычисляем возраст
        $age = $now->format('Y') - $birth->format('Y');

        // Проверяем, был ли уже день рождения в этом году
        $birthdayThisYear = new DateTime($now->format('Y') . '-' . $birth->format('m-d'));

        if ($now < $birthdayThisYear) {
            $age--; // Если день рождения ещё не наступил, уменьшаем возраст на 1
        }

        return $age;
    }

    protected function compare(array $dData, array $lData){

        $data =[];
        foreach ($lData AS $key=> $value){
            $data[$key] = $this->getAgeInCurrentYear($value['DR']);
        }
        foreach ($dData AS $key => $value){
            //$result[$key]['AGE'] = $data[$key];
            $result[$key][$data[$key]] = $value;
        }
        return $result;
    }
    protected function parseL(string $file){
        $path = $this->folder.$file;
        $xml = simplexml_load_file($path);
        $array = $this->simpleXmlToArray($xml);
        foreach ($array['PERS'] AS $key => $value){
            $result[$value['ID_PAC']] = $value;
        }
        return $result;
    }


    public function parseXML(array $files){
        $d = $this->getXmlFileName($files, '/^D/');
        $f = $this->getXmlFileName($files, '/^F/');
        $l = $this->getXmlFileName($files, '/^L/');
        $dData = $this->parseD($d);
        $lData = $this->parseL($l);
        //Здесь я получаю массив с услугами отсортированными по пациенту и возрасту
        $compare = $this->compare($dData, $lData);
        return ['D' => $d, 'F' => $f, 'L'=> $l];
    }

}