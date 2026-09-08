<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\Base\DataParser;

class SameSpecialtiesFinder
{
    private array $doctors = [
        '04397483592' => 'Кузьмина С.М.',
        '12978613304' => 'Кулагина А.А.',
        '06785590318' => 'Нагаслаева В.А.',
        '05641628460' => 'Усатых Е.В.',
        '16063971874' => 'Пинчук М.С.',
        '04400616402' => 'Мухо Л.А.',
        '12964746903' => 'Марченко Д.С.',
        '04402655624' => 'Гавриш Е.Ю.',
        '04151462819' => 'Вонгай И.П.',
        '15897783656' => 'Лобачева А.О.',
        '10218700299' => 'Бочаров Д.Е.',
        '06781975823' => 'Марценюк Р.Г.',
        '04151461716' => 'Демина О.А.',
        '15166506356' => 'Строгая Ю.Г.',
        '08695943441' => 'Козлова О.И.',
        '11930395450' => 'Сидоренко Е.А.',
        '11792268071' => 'Кушнирук Н.С.',
        '14624856174' => 'Серова Т.П.',
        '10834046834' => 'Гаврилова Ю.А.'
    ];
    public function __construct(private DataParser $parser)
    {

    }

    private function personify(array $xml, array $differentSpecialistCases){
        $personified = [];
        $i = 0;
        foreach ($xml['L']['PERS'] AS $pers) {
            $idPac = $pers['ID_PAC'];
            if (array_key_exists($pers['ID_PAC'], $differentSpecialistCases)){
                $differentSpecialistCase = $differentSpecialistCases[$idPac];
                foreach ($differentSpecialistCase['DIFFDOKT'] AS $diffDokt){
                    $personified[$i]['FAM'] = $pers['FAM'];
                    $personified[$i]['IM'] = $pers['IM'];
                    $personified[$i]['OT'] = array_key_exists('OT', $pers) ? $pers['OT'] : '';
                    $personified[$i]['DR'] = date('d.m.Y', strtotime($pers['DR']));
                    $personified[$i]['SNILS'] = $pers['SNILS'];;
                    $personified[$i]['IDDOKT'] = $this->doctors[$differentSpecialistCases[$idPac]['IDDOKT']];
                    $personified[$i]['IDDOKT-PROFIL'] = $differentSpecialistCases[$idPac]['PROFIL'];
                    $personified[$i]['IDDOKT-PRVS'] = $differentSpecialistCases[$idPac]['PRVS'];
                    $personified[$i]['DIFFDOKT'] = $this->doctors[$diffDokt['CODE_MD']];
                    $personified[$i]['DIFFDOKT-USL'] = $diffDokt['CODE_USL'];
                    $personified[$i]['DIFFDOKT-DS'] = $diffDokt['DS'];
                    $personified[$i]['DIFFDOKT-PROFIL'] = $diffDokt['PROFIL'];
                    $personified[$i]['DIFFDOKT-PRVS'] = $diffDokt['PRVS'];
                    $i++;
                }
            }
        }
        return $personified;
    }

    private function findMultipleUslCases(array $xml){
        $multipleUslCases = [];
        foreach ($xml['H']['ZAP'] as $zap) {
            foreach ($zap['Z_SL'][0]['SL'] AS $sl){
                if (count($sl['USL']) > 1){
                    $idPac = $zap['PACIENT'][0]['ID_PAC'];
                    $multipleUslCases[$idPac] = $sl;
                }
            }

        }
        return $multipleUslCases;
    }

    private function findDifferentSpecialistCases(array $multipleCases){
        $differentSpecialistCases = [];
        foreach ($multipleCases AS $idPac => $case){
            $idDokt = $case['IDDOKT'];
            foreach ($case['USL'] as $usl) {
                $codeMD = $usl['MR_USL_N'][0]['CODE_MD'];
                if ($idDokt !== $codeMD){
                    $differentSpecialistCases[$idPac]['IDDOKT'] = $idDokt;
                    $differentSpecialistCases[$idPac]['PROFIL'] = $case['PROFIL'];
                    $differentSpecialistCases[$idPac]['PRVS'] = $case['PRVS'];
                    $differentSpecialistCases[$idPac]['DIFFDOKT'][$codeMD]['CODE_MD'] = $codeMD;
                    $differentSpecialistCases[$idPac]['DIFFDOKT'][$codeMD]['CODE_USL'] = $usl['CODE_USL'];
                    $differentSpecialistCases[$idPac]['DIFFDOKT'][$codeMD]['DS'] = $usl['DS'];
                    $differentSpecialistCases[$idPac]['DIFFDOKT'][$codeMD]['PROFIL'] = $usl['PROFIL'];
                    $differentSpecialistCases[$idPac]['DIFFDOKT'][$codeMD]['PRVS'] = $usl['MR_USL_N'][0]['PRVS'];
                }
            }
        }
        return $differentSpecialistCases;
    }

    public function findSameSpecialties(){
        $xml = $this->parser->parseXML();
        $multipleUslCases = $this->findMultipleUslCases($xml);
        $differentSpecialistCases = $this->findDifferentSpecialistCases($multipleUslCases);
        $personified = $this->personify($xml, $differentSpecialistCases);
        //$ds = $this->assembleDataSet($personified);
        return $personified;
    }

}