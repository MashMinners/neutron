<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class RequiredTeethFinder
{
    public function __construct(private DataParser $parser)
    {

    }

    private $_withCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.0', '19K04.04', '19K04.05', '19K04.06',
        '19K04.4', '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51',
        '19K04.52', '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.2', '19K05.31', '19K05.32',
        '19K05.41', '19K06.8', '19K08.3', '19K10.2', '19K10.3'
    ];

    private function personify(array $xml, array $requiredCases){
        $personified = [];
        //Задача получить пару IDCASE = ID_PAC
        foreach ($xml['H']['ZAP'] AS $zap){
            $zapIdCase = $zap['Z_SL'][0]['IDCASE'];
            if (array_key_exists($zapIdCase, $requiredCases)){
                $casePacs[$zap['Z_SL'][0]['IDCASE']] =  $zap['PACIENT'][0]['ID_PAC'];
            }
        }
        //Персонифицирую случаи
        foreach ($xml['L']['PERS'] AS $pers){
            if (in_array($pers['ID_PAC'], $casePacs)){
                $personified[$pers['ID_PAC']] = $pers;
            }
        }
        //Здесь нужно получить стоматологическую информацию о диагнозе и зубах
        foreach ($xml['P']['SL'] AS $sl){
            $idCase = $sl['IDCASE'];
            if (array_key_exists($idCase, $requiredCases)){
                //Получаю необходимый ID_PAC по IDCASE
                $needleIDPac = $casePacs[$idCase];
                $personified[$needleIDPac]['CODE_USL'] = $requiredCases[$idCase]['CODE_USL'];
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
            $dataSet[$i]['ZUB'] = 'Не проставлен';
            $dataSet[$i]['CODE_USL'] = $record['CODE_USL'];
            $i++;
        }
        return $dataSet;
    }

    private function findRequiredCases(array $xml){
        $requiredCases = [];
        foreach ($xml['P']['SL'] AS $sl){
            foreach ($sl['STOM'] AS $stom){
                if (!array_key_exists('ZUB', $stom)){
                    if (in_array($stom['CODE_USL'], $this->_withCode)){
                        $requiredCases[$sl['IDCASE']] = $stom;
                    }
                }
            }
        }
        return $requiredCases;
    }

    public function findRequiredTeeth(){
        $xml = $this->parser->parseXML();
        $requiredCases = $this->findRequiredCases($xml);
        $personified = $this->personify($xml, $requiredCases);
        $ds = $this->assembleDataSet($personified);
        return $ds;
    }

}