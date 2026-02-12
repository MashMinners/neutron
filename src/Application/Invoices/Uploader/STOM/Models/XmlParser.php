<?php

namespace Application\Invoices\Uploader\STOM\Models;

use SimpleXMLElement;

class XmlParser extends \Application\XmlParser\XmlParser
{
    private $folder = 'storage/invoices/xml/stom/';

    private function parsePM(string $file){
        $path = $this->folder.$file;
        $PM = simplexml_load_file($path);
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

    private function parseLM(string $file){
        $path = $this->folder.$file;
        $LM = simplexml_load_file($path);
        $pers = $this->simpleXmlToArray($LM)['PERS'];
        return $pers;
    }

    private function parseHM(string $file){
        $path = $this->folder.$file;
        $HM = simplexml_load_file($path);
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

    public function parseXML(array $files) : array{
        $pmFile = $this->getXmlFileName($files, '/^PM/');
        $lmFile = $this->getXmlFileName($files, '/^LM/');
        $hmFile = $this->getXmlFileName($files, '/^HM/');
        $result['PM'] = $this->parsePM($pmFile);
        $result['LM'] = $this->parseLM($lmFile);
        $result['HM'] = $this->parseHM($hmFile);
        return $result;
    }

}