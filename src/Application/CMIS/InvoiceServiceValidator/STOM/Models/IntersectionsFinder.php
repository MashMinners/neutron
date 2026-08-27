<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;
use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;

class IntersectionsFinder
{
    public function __construct(private DataParser $parser)
    {

    }

    private function getXlsMatchArray(array $xls){
        $workSchema = [
            'Полис',
            'Профиль МП',
            'Дата начала',
            'Дата окончания'
        ];
        //Получаю заголовок xls
        $xlsHeader = array_shift($xls);
        /**
         * Так как xls выгружаемый на разных компах зависит от настроенного профиля, то поиск по столбцам(буквам) некорретен
         * Значит поиск будет происходить по заголовку, где заголовок = буква которая указывает на столбец озаглавленный заголовком
         * Например Полис => 'D'
         */
        $excelFieldsKeys = $this->parser->getExcelFieldsKeys($workSchema, $xlsHeader);
        $array = [];
        foreach ($xls AS $single){
            $array[$single[$excelFieldsKeys['Полис']].'-'.(int)$single[$excelFieldsKeys['Профиль МП']]]['DATE_IN'] = strtotime($single[$excelFieldsKeys['Дата начала']]);
            $array[$single[$excelFieldsKeys['Полис']].'-'.(int)$single[$excelFieldsKeys['Профиль МП']]]['DATE_OUT'] = strtotime($single[$excelFieldsKeys['Дата окончания']]);
        }
        return $array;
    }

    private function getXmlMatchArray(array $xml){
        $result = [];
        foreach ($xml['H']['ZAP'] AS $single){
            foreach ($single['Z_SL'][0]['SL'][0]['USL'] AS $usl){
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['DATE_IN'] = strtotime($usl['DATE_IN']);
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['DATE_OUT'] = strtotime($usl['DATE_OUT']);
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['PROFIL'] = $usl['PROFIL'];
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['CODE_USL'] = $usl['CODE_USL'];
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['ENP'] = $single['PACIENT'][0]['ENP'];
                $result[$single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL']]['ID_PAC'] = $single['PACIENT'][0]['ID_PAC'];
            }
        }
        $array = [];
        foreach ($result AS $key => $value){
            foreach ($xml['L']['PERS'] AS $pers){
                if ($pers['ID_PAC'] === $value['ID_PAC']){
                    $value['FAM'] = $pers['FAM'];
                    $value['IM'] = $pers['IM'];
                    $value['OT'] = $pers['OT'];
                    $value['DR'] = date('d.m.Y', strtotime($pers['DR']));
                    $value['SNILS'] = $pers['SNILS'];
                }
                $array[$key] = $value;
            }
        }
        return $array;
    }

    private function compareXmlAndXls(array $xml, array $xls){
        $compared = array_intersect_key($xml, $xls);
        $intersections = [];
        foreach ($compared AS $key => $value){
            $value['DATE_IN_TFOMS'] = $xls[$key]['DATE_IN'];
            $value['DATE_OUT_TFOMS'] = $xls[$key]['DATE_OUT'];
            //Поиск пересечений за 30 дней по разнице в секундах
            $dateDiff = $value['DATE_IN'] - $value['DATE_OUT_TFOMS'];//$xls[$key]['DATE_IN'] - $value['DATE_OUT_TFOMS'];
            if ($dateDiff < 2592000){
                $intersections[$key] = $value;
                //Конвертация дат в удобьный вид
                $intersections[$key]['DATE_IN'] = date('d.m.Y', $value['DATE_IN']);
                $intersections[$key]['DATE_OUT'] = date('d.m.Y', $value['DATE_OUT']);
                $intersections[$key]['DATE_IN_TFOMS'] = date('d.m.Y', $value['DATE_IN_TFOMS']);
                $intersections[$key]['DATE_OUT_TFOMS'] = date('d.m.Y', $value['DATE_OUT_TFOMS']);
            }
        }
        return $intersections;
    }

    private function assembleDataSet(array $intersections) : array{
        $dataSet = [];
        $i = 0;
        foreach ($intersections AS $intersection){
            $dataSet[$i]['DATE_IN_TFOMS'] = $intersection['DATE_IN_TFOMS'];
            $dataSet[$i]['DATE_OUT_TFOMS'] = $intersection['DATE_OUT_TFOMS'];
            $dataSet[$i]['DATE_IN'] = $intersection['DATE_IN'];
            $dataSet[$i]['DATE_OUT'] = $intersection['DATE_OUT'];
            $dataSet[$i]['FAM'] = $intersection['FAM'];
            $dataSet[$i]['IM'] = $intersection['IM'];
            $dataSet[$i]['OT'] = $intersection['OT'];
            $dataSet[$i]['DR'] = $intersection['DR'];
            $dataSet[$i]['ENP'] = $intersection['ENP'];
            $dataSet[$i]['SNILS'] = $intersection['SNILS'];
            $i++;
        }
        return $dataSet;
    }

    public function findIntersections(){
        $xls = $this->parser->parseExcel();
        $xml = $this->parser->parseXML();
        $xlsForMatch = $this->getXlsMatchArray($xls);
        $xmlForMatch = $this->getXmlMatchArray($xml);
        $intersections = $this->compareXmlAndXls($xmlForMatch, $xlsForMatch);
        $dataSet = $this->assembleDataSet($intersections);
        return $dataSet;
    }

}