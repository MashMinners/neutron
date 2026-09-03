<?php

namespace Application\TFOMS\MedicalBillingValidator\STAC\Models;

use Application\TFOMS\MedicalBillingValidator\Base\DataParser;
use Application\TFOMS\MedicalBillingValidator\Base\ResultFileMaker;

/**
 * Данный класс принимает на вход данные в виде .ods в которых прописаны случаи заливки и отказов из ТФОМС
 * Далее он находит уникальное количество случаев которые поданы, сколько из них успешно залито и сколько не приняты
 * ФОНДом к оплате т.е. они не попадут в счета. Так же он показывает случае исключенные из оплаты сотрудниками ФОНДа
 */
class Validator
{
    public function __construct(private DataParser $parser, private ResultFileMaker $maker){

    }
    private function getDefected($data){
        $defected =[];
        foreach ($data AS $key => $value){
            if ($value['H'] === '1'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $defected[$uniqueId] = $value;
            }
        }
        return $defected;
    }

    private function getSuccessful($data){
        $successful =[];
        foreach ($data AS $key => $value){
            //if ($value['H'] === '0' && $value['I'] === '0'){
            if ($value['H'] === '0'){
                $uniqueId = $value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $successful[$uniqueId] = $value;
            }
        }
        return $successful;
    }

    private function getTakenForPayment(array $data){
        $successful =[];
        foreach ($data AS $key => $value){
            if ($value['H'] === '0' && $value['I'] === '0'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $successful[$uniqueId] = $value;
            }
        }
        return $successful;
    }

    private function getCanceled($data){
        $canceled =[];
        foreach ($data AS $key => $value){
            if ($value['I'] === '1'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $canceled[$uniqueId] = $value;
            }
        }
        return $canceled;
    }

    private function getNonReturn(array $standard, array $successful, array $defective){
        $nonReturn = [];
        foreach ($standard as $key => $name) {
            //Successful
            $hasZero = isset($successful[$key]) && $successful[$key] > 0;
            //Defected
            $hasOne = isset($defective[$key]) && $defective[$key] > 0;
            // Невозврат: есть хотя бы одна 1, но нет ни одного 0
            if ($hasOne && !$hasZero) {
                $nonReturn[$key] = $name;
            }
        }
        return $nonReturn;
    }

    private function getUniqueCase(array $data){
        $unique = [];
        foreach ($data AS $item){
            $uniqueId = $item['C'].'-'.$item['D'].'-'.$item['N'].'-'.$item['O'].'-'.$item['P'];
            $unique[$uniqueId] = $item;
        }
        return $unique;
    }

    private function indicateWithUnique(array $result){
        $indicated = [];
        foreach ($result as $item) {
            /**
             * Был пациент Коновалов, совпадал по всем полям, кроме поля А (оказывается в один и тот же день, был у двух врачей)
             * потому и поле "Код случая отличался
             * Поля О и Р нужны так же, так как в стационаре коды случаев идут как 1,2,3 и тд, соответственно и определить уникальность можно по дате
             * нахождения в стационаре
             */
            $uniqueId = $item['B'].'-'.$item['C'].'-'.$item['D'].'-'.$item['H'].'-'.$item['O'].'-'.$item['P'];
            $indicated[$uniqueId] = $item;
        }
        return $indicated;
    }

    private function unify(array $unique){
        $standard = [];
        foreach ($unique AS $key => $value){
            $uniqueId = $value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['N'];
            $standard[$uniqueId] = $value;
        }
        return $standard;
    }

    public function validate(){
        //Получить данные из всех файлов в папке
        $excelData = $this->parser->getExcelData();
        //Получить только уникальные записи исключив двойники по проблемным загрузкам
        $unique = $this->indicateWithUnique($excelData);
        //Получить все записи с ошибками при загрузках
        $defected = $this->getDefected($unique);
        //Получить все записи с успешными загрузками
        $successful = $this->getSuccessful($unique);
        //Получить данные по удаленным из оплаты записям
        $canceled = $this->getCanceled($unique);
        //Получаю те записи которые фонд принял на оплату
        $forPayment = $this->getTakenForPayment($successful);
        //На выходе должно получаться столько же записей,сколько и вошло, но с унифицированным идентификатором
        $unifiedUnique = $this->unify($unique);
        //На выходе должно получаться столько же записей,сколько и вошло, но с унифицированным идентификатором
        $unifiedSuccessful = $this->unify($successful);
        /**
         * На выходе должно получаться МЕНЬШЕ записей,чем вошло и с унифицированным идентификатором.
         * Записей будет меньше в том случае, если были случаи переподаны в ФОНД и они снова повторялись с отказами
         */
        $unifyDefected = $this->unify($defected);
        /**
         * Здесь уже пошагово кажду запись сравнивают с уникальными строками и если такая запись есть в дефектных,
         * но нет в успешных, то считается что запись не будет подана на оплату ибо не прошла проверки ТФОМС
         */
        $nonReturn = $this->getNonReturn($unifiedUnique, $unifiedSuccessful, $unifyDefected);
        $this->maker->generateExcel($defected, 'Defected');
        $this->maker->generateExcel($successful, 'Successful');
        $this->maker->generateExcel($nonReturn, 'Non Return');
        $this->maker->generateExcel($canceled, 'Canceled');
        $this->maker->generateExcel($forPayment, 'For Payment');
        return [
            'Всего записей' => count($excelData),
            'Уникальные случаи' => count($unique),
            'Случаи залитые без ошибок в реестре' =>  count($successful),
            'Приняты на оплату' => count($forPayment),
            'Удалены из оплаты' =>  count($canceled),
            'Не поданы на оплату' =>  count($nonReturn)
        ];
    }

}