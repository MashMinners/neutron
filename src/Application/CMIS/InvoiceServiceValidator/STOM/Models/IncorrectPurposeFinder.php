<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class IncorrectPurposeFinder
{
    public function __construct(private DataParser $parser)
    {

    }

    private function assembleDataSet(array $records) : array{
        $dataSet = [];
        $i = 0;
        foreach ($records AS $record){
            $dataSet[$i]['DATE_IN'] = $record['DATE_IN'];
            $dataSet[$i]['DATE_OUT'] = $record['DATE_OUT'];
            $dataSet[$i]['FAM'] = $record['FAM'];
            $dataSet[$i]['IM'] = $record['IM'];
            $dataSet[$i]['OT'] = $record['OT'];
            $dataSet[$i]['DR'] = $record['DR'];
            $dataSet[$i]['ENP'] = $record['ENP'];
            $dataSet[$i]['SNILS'] = $record['SNILS'];
            $dataSet[$i]['P_CEL'] = $record['P_CEL'];
            $dataSet[$i]['DS'] = $record['DS'];
            $i++;
        }
        return $dataSet;
    }

    public function findIncorrectPurposes(){
        $xml = $this->parser->parseXML();
        $incorrectPurposes = [];
        foreach ($xml['H']['ZAP'] AS $single){
            foreach ($single['Z_SL'][0]['SL'][0]['USL'] AS $usl){
                $uslCount = count($single['Z_SL'][0]['SL'][0]['USL']);
                $uniqueID = $single['PACIENT'][0]['ENP'].'-'.$usl['PROFIL'];
                $pCEL = $single['Z_SL'][0]['SL'][0]['P_CEL'];
                if ($uslCount  === 1 AND $usl['DS'] === 'Z01.2' AND $pCEL === '3.0'){
                    $incorrectPurposes[$uniqueID]['DATE_IN'] = date('d.m.Y', strtotime($usl['DATE_IN']));
                    $incorrectPurposes[$uniqueID]['DATE_OUT'] = date('d.m.Y', strtotime($usl['DATE_OUT']));
                    $incorrectPurposes[$uniqueID]['PROFIL'] = $usl['PROFIL'];
                    $incorrectPurposes[$uniqueID]['CODE_USL'] = $usl['CODE_USL'];
                    $incorrectPurposes[$uniqueID]['DS'] = $usl['DS'];
                    $incorrectPurposes[$uniqueID]['ENP'] = $single['PACIENT'][0]['ENP'];
                    $incorrectPurposes[$uniqueID]['ID_PAC'] = $single['PACIENT'][0]['ID_PAC'];
                    $incorrectPurposes[$uniqueID]['P_CEL'] = $pCEL;
                }
            }
        }
        $array = [];
        foreach ($incorrectPurposes AS $key => $value){
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
        $dataSet = $this->assembleDataSet($array);
        return $dataSet;
    }

}