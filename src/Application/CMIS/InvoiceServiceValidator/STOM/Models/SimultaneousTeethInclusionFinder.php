<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Models;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\DataParser;

class SimultaneousTeethInclusionFinder
{
    public function __construct(private DataParser $parser)
    {

    }

    private $_simultaneousCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.04', '19K04.05', '19K04.06', '19K04.4',
        '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51', '19K04.52',
        '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.31', '19K05.41', '19K10.2', '19K10.3'
    ];

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
        '10218700299' => 'Бочаров Д.Е.'
    ];

    /**
     * Ищу те случаи, где пролечено более 1-го зуба
     * @param array $xml
     * @return array
     */
    private function getMultipleStom(array $xml) : array{
        $multipleStom = [];
        foreach ($xml['P']['SL'] AS $sl){
            if(count($sl['STOM']) > 1){
                $multipleStom[$sl['IDCASE']] = $sl['STOM'];
            }
        }
        return $multipleStom;
    }

    private function findSimultaneousCases(array $multipleStom){
        $simultaneousCases = [];
        foreach ($multipleStom as $idCase => $value) {
            $simultaneousTeethInclusion = [];
            foreach ($value AS $stom){
                if (in_array($stom['CODE_USL'], $this->_simultaneousCode) AND array_key_exists('ZUB', $stom) AND $stom['ZUB'] !==''){
                    $simultaneousTeethInclusion[] = $stom['ZUB'];
                }
            }
            /**
             * В массив zub попадает название зуба типа НЛ6, только для тех зубов диагнозы которых совпадают с массивом
             * $_simultaneousCode
             * Массив может выглядеть так:
             * $zub = [1 = НП6, 2 = НП7] или так $zub = [1 = НП6, 2 = НП6]
             * Далее идет сравненение, если количество элементов массива $zub больше чем количество уникальных значений,
             * т.е. array_unique() - удаляет дубликаты из $zub, тем самым обозначая что в оригинальном массиве $zub было
             * два диагноза из $_simultaneousCode, которые были присвоены одному и тому же зубу, допустим НП6
             * Тем самым если оригинальный массив больше, чем обработанный с учетом удаления дубликатов, значит в массив
             * мы добавляем ID случая, в котором было такое совпадение
             */
            if (count($simultaneousTeethInclusion) > count(array_unique($simultaneousTeethInclusion))){
                $simultaneousCases[] = $idCase;
            }
        }
        return $simultaneousCases;
    }

    private function personify(array $xml, array $simultaneousCases){
        $personified = [];
        //Задача получить пару IDCASE = ID_PAC
        foreach ($xml['H']['ZAP'] AS $zap){
            if (in_array($zap['Z_SL'][0]['IDCASE'], $simultaneousCases)){
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
            if(in_array($idCase, $simultaneousCases)){
                //Получаю необходимый ID_PAC по IDCASE
                $needleIDPac = $casePacs[$idCase];
                $personified[$needleIDPac]['STOM'] = $sl['STOM'];
            }
        }
        foreach ($xml['H']['ZAP'] AS $zap){
            $idCase = $zap['Z_SL'][0]['IDCASE'];
            $sl = $zap['Z_SL'][0]['SL'][0];
            if(in_array($idCase, $simultaneousCases)){
                $needleIDPac = $casePacs[$idCase];
                //Врач создавший случай, открывший талон (не нужен, но добавлен дабы сохранить логику поиска на будущее)
                $personified[$needleIDPac]['IDDOKT'] = $this->doctors[$sl['IDDOKT']];
            }
        }
        return $personified;
    }

    private function assembleDataSet(array $records){
        $dataSet = [];
        foreach ($records AS $record){
            foreach ($record['STOM'] AS $stom){
                if(array_key_exists('ZUB', $stom)){
                    $uniqueID = $record['SNILS'].'-'.$stom['ZUB'];
                    $dataSet[$uniqueID]['FAM'] = $record['FAM'];
                    $dataSet[$uniqueID]['IM'] = $record['IM'];
                    $dataSet[$uniqueID]['OT'] = $record['OT'];
                    $dataSet[$uniqueID]['DR'] = date('d.m.Y', strtotime($record['DR']));
                    $dataSet[$uniqueID]['SNILS'] = $record['SNILS'];
                    $dataSet[$uniqueID]['CODE_USL'] = $stom['CODE_USL'];
                    $dataSet[$uniqueID]['IDDOKT'] = $record['IDDOKT'];
                    $dataSet[$uniqueID]['ZUB'][$stom['ZUB']][] = $stom['CODE_USL'];
                }
            }
        }
        $needle = [];
        $i = 0;
        foreach ($dataSet AS $single){
            foreach ($single['ZUB'] AS $teeth => $diagnosis){
                if (count($diagnosis) > 1){
                    foreach ($diagnosis AS $singleDiag){
                        $needle[$i]['FAM'] = $single['FAM'];
                        $needle[$i]['IM'] = $single['IM'];
                        $needle[$i]['OT'] = $single['OT'];
                        $needle[$i]['DR'] = $single['DR'];
                        $needle[$i]['SNILS'] = $single['SNILS'];
                        $needle[$i]['ZUB'] = $teeth;
                        $needle[$i]['CODE_USL'] = $singleDiag;
                        $needle[$i]['IDDOKT'] = $single['IDDOKT'];
                        $i++;
                    }
                }
            }
        }
        return $needle;
    }

    public function findSimultaneousTeethInclusion(){
        $xml = $this->parser->parseXML();
        $multipleStom = $this->getMultipleStom($xml);
        $simultaneousCases = $this->findSimultaneousCases($multipleStom);
        $p = $this->personify($xml, $simultaneousCases);
        $ds = $this->assembleDataSet($p);
        return $ds;
    }

}