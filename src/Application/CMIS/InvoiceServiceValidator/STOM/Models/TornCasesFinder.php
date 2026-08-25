<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class TornCasesFinder
{
    public function __construct(private DataParser $parser)
    {

    }
    private function findRecurringPatients(array $xml){
        $enp = [];
        foreach ($xml['H']['ZAP'] AS $single){
            $enp[] = $single['PACIENT'][0]['ID_PAC'];
        }
        $counts = array_count_values($enp);
        $duplicates = array_keys(array_filter($counts, fn($count) => $count > 1));
        $recurring = [];
        foreach ($xml['L']['PERS'] AS $single){
            $idPac = $single['ID_PAC'];
            if (in_array($idPac, $duplicates)){
                $recurring[$single['ID_PAC']] = $single;
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
            $i++;
        }
        return $dataSet;
    }
    public function findTornCases(){
        $xml = $this->parser->parseXML();
        $recurring = $this->findRecurringPatients($xml);
        $dataSet = $this->assembleDataSet($recurring, $xml['L']['PERS']);
        return $dataSet;
    }

}