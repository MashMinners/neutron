<?php

namespace Application\XMLParser\Models;

use SimpleXMLElement;

class XMLParser
{
    private $_withoutCode = [
        '19ALL.0', '19B00.2', '19B37.0', '19D10.1', '19D10.10', '19K03.0', '19K03.6', '19K05.0', '19L05.1', '19K05.3',
        '19L05.4', '19K07.5', '19K07.6', '19K11.2', '19K12.0', '19K12.1', '19K13.0', '19K13.2', '19K14.0', '19K14.1',
        '19K14.6', '19L43.3', '19M12.8', '19S00.5', '19S00.7', '19S01.4', '19S01.5', '19S02.6', '19Z01.2', '19Z01.21',
        '19Z01.22', '19Z01.23'
    ];
    private function zubFilter($PM){
        $withoutCodes = [];
        foreach ($PM->SL AS $sl){
            if ($sl->STOM->ZUB){
                if (in_array($sl->STOM->CODE_USL, $this->_withoutCode)){
                    $withoutCodes[] = $sl;
                }
            }
        }
        return $withoutCodes;
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
    private function parsePM(SimpleXMLElement $PM){
        $pmSL = [];
        $pmArray = $this->simpleXmlToArray($PM);
        foreach ($pmArray['SL'] as $sl) {
            /**
             * Тут вся проблема в том, что на 1 Услугу может быть 2 УЕТа. Кулагина сама того не зная сказала что у нее
             * так было. Тоесть помимо кариеса подтянулся еще УЕТ по осмотру
             */
            //Значит если количество услуг совпадает с количеством ует то будет такой алгоритм
            if (count($sl['STOM']) === count($sl['USL'])){
                foreach ($sl['STOM'] as $key => $value){
                    $sl['STOM'][$key]['IDSERV'] = $sl['USL'][$key]['IDSERV'];
                    $sl['STOM'][$key]['SL_ID'] = $sl['SL_ID'];
                    $sl['STOM'][$key]['IDCASE'] = $sl['IDCASE'];
                    $sl['STOM'][$key]['ZUB'] = $sl['STOM'][$key]['ZUB'] ?? null;
                }
            }
            //В противном случае алогоритм будет таким, что во все уеты будет проставлятся код одной единственной услуги
            else{
                foreach ($sl['STOM'] as $key => $value){
                    $sl['STOM'][$key]['IDSERV'] = $sl['USL'][0]['IDSERV'];
                    $sl['STOM'][$key]['SL_ID'] = $sl['SL_ID'];
                    $sl['STOM'][$key]['IDCASE'] = $sl['IDCASE'];
                    $sl['STOM'][$key]['ZUB'] = $sl['STOM'][$key]['ZUB'] ?? null;
                }
            }
            unset($sl['USL']);
            $pmSL[] = $sl;
        }
        return $pmSL;
    }
    private function parseHM(SimpleXMLElement $HM){
        $hmZAP = [];
        $hmArray = $this->simpleXmlToArray($HM);
        foreach ($hmArray['ZAP'] AS $zap){
            //Перенесети данные из PACIENT в Z_SL, пациент в любом случае один будет.
            //А вот Z_SL это я так понимаю законченный случай и их может быть несколько, тогда надо будет перебрать через foreach
            foreach ($zap['Z_SL'] AS $z_sl){
                $z_sl['ID_PAC'] = $zap['PACIENT'][0]['ID_PAC'];
                $z_sl['VPOLIS'] = $zap['PACIENT'][0]['VPOLIS'];
                $z_sl['ENP'] = $zap['PACIENT'][0]['ENP'];
                $z_sl['SMO'] = $zap['PACIENT'][0]['SMO'];
                $z_sl['NOVOR'] = $zap['PACIENT'][0]['NOVOR'];
                $z_sl['SOC'] = $zap['PACIENT'][0]['SOC'];
                $z_sl['DATE_Z_1'] = strtotime($z_sl['DATE_Z_1']);
                $z_sl['DATE_Z_2'] = strtotime($z_sl['DATE_Z_2']);
                foreach ($z_sl['SL'] AS $sl){
                    $sl['IDCASE'] = $z_sl['IDCASE'];
                    $sl['DATE_1'] = strtotime($sl['DATE_1']);
                    $sl['DATE_2'] = strtotime($sl['DATE_2']);
                    $sl['C_ZAB'] = $sl['C_ZAB'] ?? null;
                    $sl['USL_COUNT'] = count($sl['USL']);
                    foreach ($sl['USL'] AS $usl){
                        $usl['SL_ID'] = $sl['SL_ID'];
                        $usl['DATE_IN'] = strtotime($usl['DATE_IN']);
                        $usl['DATE_OUT'] = strtotime($usl['DATE_OUT']);
                        array_shift( $sl['USL']);
                        $sl['USL'][] = $usl;
                    }
                    $z_sl['SL'] = $sl;
                }
                array_shift($zap['Z_SL']);
                $zap['Z_SL'][] = $z_sl;
            }
            $hmZAP[] = $zap;
        }
        return $hmZAP;
    }
    private function parseLM(SimpleXMLElement $LM){
        $pers = $this->simpleXmlToArray($LM)['PERS'];
        return $pers;
    }
    private function formatLM(array $LM){
        foreach ($LM as $case){
            $case['DR'] = strtotime($case['DR']);
            //$formattedCases[$case[0]] = $case;
            $formattedCases[] = $case;
        }
        return $formattedCases;
    }
    public function parse() : array{
        $HM = simplexml_load_file('storage/HM.xml');
        //
        $PM = simplexml_load_file('storage/PM.xml');
        //Персональные данные пациента и ID_PAC
        $LM = simplexml_load_file('storage/LM.xml');
        $LM = $this->parseLM($LM);
        $result['PM'] = $this->parsePM($PM);
        $result['HM'] = $this->parseHM($HM);
        $result['LM']= $this->formatLM($LM);


        return $result;
    }

}