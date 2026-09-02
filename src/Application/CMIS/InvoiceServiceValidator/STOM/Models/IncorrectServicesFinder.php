<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class IncorrectServicesFinder
{
    public function __construct(private DataParser $parser)
    {

    }

    private function getMultipleCases(array $xml){
        $multipleCases = [];
        foreach ($xml['H']['ZAP'] AS $single){
            $cases = $single['Z_SL'][0]['SL'][0]['USL'];
            if (count($cases) > 1){
                $arr = [];
                foreach ($cases AS $case){
                   $arr[] = $case['CODE_USL'];
                }
                //Если в случае отсуствует и B01.065.007 и B01.065.003 это значит, что в случае нет первичной услуги
                if (!in_array('B01.065.003',$arr) AND !in_array('B01.067.001',$arr)
                    AND !in_array('B01.065.001',$arr) AND !in_array('B01.064.003',$arr)
                    AND !in_array('B01.065.007',$arr)){
                    $multipleCases['haveNoPrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                }
                //Если в случае присутсвует более двух первичных услуг B01.065.003 -это ошибка
                elseif(in_array('B01.065.003',$arr)){
                    $counts = array_count_values($arr);
                    $search = 'B01.065.003';
                    $B01Count = $counts[$search] ?? 0;
                    if ($B01Count > 1) {
                        $multipleCases['twoOrMorePrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                    }
                }
                //Если в случае присутсвует более двух первичных услуг B01.067.001 -это ошибка
                elseif(in_array('B01.067.001',$arr)){
                    $counts = array_count_values($arr);
                    $search = 'B01.067.001';
                    $B01Count = $counts[$search] ?? 0;
                    if ($B01Count > 1) {
                        $multipleCases['twoOrMorePrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                    }
                }
                //Если в случае присутсвует более двух первичных услуг B01.065.001 - это ошибка
                elseif(in_array('B01.065.001',$arr)){
                    $counts = array_count_values($arr);
                    $search = 'B01.065.001';
                    $B01Count = $counts[$search] ?? 0;
                    if ($B01Count > 1) {
                        $multipleCases['twoOrMorePrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                    }
                }
                //Если в случае присутсвует более двух первичных услуг B01.064.003 - это ошибка
                elseif(in_array('B01.064.003',$arr)){
                    $counts = array_count_values($arr);
                    $search = 'B01.064.003';
                    $B01Count = $counts[$search] ?? 0;
                    if ($B01Count > 1) {
                        $multipleCases['twoOrMorePrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                    }
                }
                //Если в случае присутсвует более двух первичных услуг B01.065.007 - это ошибка
                else{
                    $counts = array_count_values($arr);
                    $search = 'B01.065.007';
                    $B01Count = $counts[$search] ?? 0;
                    if ($B01Count > 1) {
                        $multipleCases['twoOrMorePrimary'][] = $single['PACIENT'][0]['ID_PAC'];
                    }
                }
            }
        }
        return $multipleCases;
    }

    private function personify(array $xml, array $multipleCases){
        $personified = [];
        foreach ($xml['L']['PERS'] AS $single){
            $idPac = $single['ID_PAC'];
            if (in_array($idPac, $multipleCases)){
                $personified[$single['ID_PAC']] = $single;
            }
        }
        return $personified;
    }

    private function assembleDataSet(array $records){
        $dataSet = [];
        $i = 0;
        foreach ($records AS $record){
            $dataSet[$i]['FAM'] = $record['FAM'];
            $dataSet[$i]['IM'] = $record['IM'];
            $dataSet[$i]['OT'] = array_key_exists('OT', $record) ? $record['OT'] : '';
            $dataSet[$i]['DR'] = date('d.m.Y', strtotime($record['DR']));
            $dataSet[$i]['SNILS'] = $record['SNILS'];
            $i++;
        }
        return $dataSet;
    }

    public function findIncorrectServices() : array{
        $xml = $this->parser->parseXML();
        $multipleCases = $this->getMultipleCases($xml);
        $twoOrMorePrimary = $this->personify($xml, $multipleCases['twoOrMorePrimary']);
        $haveNoPrimary = $this->personify($xml, $multipleCases['haveNoPrimary']);
        $dataSet['twoOrMorePrimary'] = $this->assembleDataSet($twoOrMorePrimary);
        $dataSet['haveNoPrimary'] = $this->assembleDataSet($haveNoPrimary);
        return $dataSet;
    }

}