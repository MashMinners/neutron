<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class IncorrectTeethCodeInclusionFinder
{
    public function __construct(private DataParser $parser)
    {

    }
    private $_withoutCode = [
        '19ALL.0', '19B00.2', '19B37.0', '19D10.1', '19D10.10', '19K03.0', '19K03.6', '19K05.0', '19L05.1', '19K05.3',
        '19L05.4', '19K07.5', '19K07.6', '19K11.2', '19K12.0', '19K12.1', '19K13.0', '19K13.2', '19K14.0', '19K14.1',
        '19K14.6', '19L43.3', '19M12.8', '19S00.5', '19S00.7', '19S01.4', '19S01.5', '19S02.6', '19Z01.2', '19Z01.21',
        '19Z01.22', '19Z01.23', '19K05.4'
    ];

    private function personify(array $xml, array $inclusionCases){
        $personified = [];
        //Задача получить пару IDCASE = ID_PAC
        foreach ($xml['H']['ZAP'] AS $zap){
            $zapIdCase = $zap['Z_SL'][0]['IDCASE'];
            if (array_key_exists($zapIdCase, $inclusionCases)){
                $casePacs[$zap['Z_SL'][0]['IDCASE']] =  $zap['PACIENT'][0]['ID_PAC'];
            }
            //if (in_array($zap['Z_SL'][0]['IDCASE'], $inclusionCases)){
                //$casePacs[$zap['Z_SL'][0]['IDCASE']] =  $zap['PACIENT'][0]['ID_PAC'];
            //}
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
            if (array_key_exists($idCase, $inclusionCases)){
                //Получаю необходимый ID_PAC по IDCASE
                $needleIDPac = $casePacs[$idCase];
                $personified[$needleIDPac]['ZUB'] = $inclusionCases[$idCase]['ZUB'];
                $personified[$needleIDPac]['CODE_USL'] = $inclusionCases[$idCase]['CODE_USL'];
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
            $dataSet[$i]['ZUB'] = $record['ZUB'];
            $dataSet[$i]['CODE_USL'] = $record['CODE_USL'];
            $i++;
        }
        return $dataSet;
    }

    private function findInclusionCases(array $xml){
        $incorrectTeethCodeInclusion = [];
        foreach ($xml['P']['SL'] AS $sl){
            foreach ($sl['STOM'] AS $stom){
                if (array_key_exists('ZUB', $stom)){
                    if (in_array($stom['CODE_USL'], $this->_withoutCode)){
                        $incorrectTeethCodeInclusion[$sl['IDCASE']] = $stom;
                    }
                }
            }
        }
        return $incorrectTeethCodeInclusion;
    }

    public function findIncorrectTeethCodeInclusion(){
        $xml = $this->parser->parseXML();
        $inclusionCases = $this->findInclusionCases($xml);
        $personified = $this->personify($xml, $inclusionCases);
        $ds = $this->assembleDataSet($personified);
        return $ds;
    }

}