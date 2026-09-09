<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class TornCasesFinder
{
    public function __construct(private DataParser $parser)
    {

    }
    private function findRecurringPatients(array $xml){
        $idPacs = [];
        foreach ($xml['H']['ZAP'] AS $zap){
            $idPacs[] = $zap['PACIENT'][0]['ID_PAC'];
            $enp[$zap['PACIENT'][0]['ID_PAC']] = array_key_exists('ENP', $zap['PACIENT'][0]) ? $zap['PACIENT'][0]['ENP'] : '';
        }
        $counts = array_count_values($idPacs);
        $duplicates = array_keys(array_filter($counts, fn($count) => $count > 1));
        $recurring = [];
        foreach ($xml['L']['PERS'] AS $pers){
            $idPac = $pers['ID_PAC'];
            $pers['ENP'] = $enp[$idPac];
            if (in_array($idPac, $duplicates)){
                $recurring[$pers['ID_PAC']] = $pers;
            }
        }
        return $recurring;
    }

    private function assembleDataSet(array $records){
        $dataSet = [];
        $i = 0;
        foreach ($records AS $record){
            $dataSet[$i]['FAM'] = $record['FAM'];
            $dataSet[$i]['IM'] = $record['IM'];
            $dataSet[$i]['OT'] = $record['OT'];
            $dataSet[$i]['DR'] = date('d.m.Y', strtotime($record['DR']));
            $dataSet[$i]['SNILS'] = $record['SNILS'];
            $dataSet[$i]['ENP'] = $record['ENP'];
            $i++;
        }
        return $dataSet;
    }
    public function findTornCases(){
        $xml = $this->parser->parseXML();
        $recurring = $this->findRecurringPatients($xml);
        $dataSet = $this->assembleDataSet($recurring);
        return $dataSet;
    }

}