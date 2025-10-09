<?php

namespace Application\XMLParser\Models;

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
    public function parse(){
        $HM = simplexml_load_file('storage/HM.xml');
        //
        $PM = simplexml_load_file('storage/PM.xml');
        //Персональные данные пациента и ID_PAC
        $LM = simplexml_load_file('storage/LM.xml');
        //$result = $this->zubFilter($PM);
        $IDs = [];
        foreach ($LM->PERS AS $pers){
            $id = (string)$pers->ID_PAC;
            $all['PERS'][$id] = $pers;
            $PERSON[] = $pers;
            $IDs[] = $id;
        }
        foreach ($PERSON AS $single){
            $single[] = 2;
        }

        return true;
    }

}