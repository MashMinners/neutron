<?php

namespace Application\Invoices\STOM\Models;

use Engine\Database\IConnector;

class IncorrectServicesFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function getSortedUsl() : array {
        //Достаем все оказанные услуги
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl_usl");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        //Разсоритровываю услуги по типу первичная/повторная - B01.065.003/B01.065.004
        $sortedArray = [];
        foreach ($result AS $single){
            $sortedArray[$single['stom_xml_hm_zsl_sl_usl_sl_id']][$single['stom_xml_hm_zsl_sl_usl_code_usl']][] = $single;
        }
        return $sortedArray;
    }

    private function getErrorsFromSorted(array $sortedArray) : array{
        /**
         * Проверяю количество первичных приемов, если более чем 1 то случай ошибка
         * Если B01.065.003 вообще нет то это тоже ошибка
         */
        $errors = [];
        foreach ($sortedArray AS $key => $value){
            //Если в случае отсуствует и B01.065.007 иB01.065.003 это значит, что в случае нет первичной услуги
            if (!array_key_exists('B01.065.007',$value) AND !array_key_exists('B01.065.003',$value)){
                $errors['haveNoPrimary'][] = $key;
            }
            //Если в случае присутсвует более двух первичных услуг B01.065.007 -это ошибка
            elseif(array_key_exists('B01.065.007',$value)){
                $B01Count = count($value['B01.065.007']);
                if ($B01Count > 1) {
                    $errors['twoOrMorePrimary'][] = $key;
                }
            }
            //Если в случае присутсвует более двух первичных услуг B01.065.003 - это ошибка
            else{
                $B01Count = count($value['B01.065.003']);
                if ($B01Count > 1) {
                    $errors['twoOrMorePrimary'][] = $key;
                }
            }
        }
        return $errors;
    }

    private function getTwoOrMorePrimaryError(array $errors) : array{
        //Найти ФИО тех пациентов у которых найдены ошибки по 2 и более первичным услугам
        $slIDs = $this->wrapWithSingleQuotes($errors['twoOrMorePrimary']);
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl AS SL
                   INNER JOIN stom_xml_hm_zsl AS ZSL ON stom_xml_hm_zsl_idcase = SL.stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_lm AS LM ON stom_xml_lm__id_pac = ZSL.stom_xml_hm_zsl_id_pac
                   WHERE stom_xml_hm_zsl_sl_sl_id IN ($slIDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result AS $key => $value){
            $result[$key]['stom_xml_hm_zsl_sl_date_1'] = date('d.m.Y', $value['stom_xml_hm_zsl_sl_date_1']);
            $result[$key]['stom_xml_lm_dr'] = date('d.m.Y', $value['stom_xml_lm_dr']);
        }
        return $result;
    }

    private function getHaveNoPrimary(array $errors) : array {
        //Найти ФИО тех пациентов у которых найдены ошибки по отсуствующей услуге B01.065.003
        $slIDs = $this->wrapWithSingleQuotes($errors['haveNoPrimary']);
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl AS SL
                   INNER JOIN stom_xml_hm_zsl AS ZSL ON stom_xml_hm_zsl_idcase = SL.stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_lm AS LM ON stom_xml_lm__id_pac = ZSL.stom_xml_hm_zsl_id_pac
                   WHERE stom_xml_hm_zsl_sl_sl_id IN ($slIDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result AS $key => $value){
            $result[$key]['stom_xml_hm_zsl_sl_date_1'] = date('d.m.Y', $value['stom_xml_hm_zsl_sl_date_1']);
            $result[$key]['stom_xml_lm_dr'] = date('d.m.Y', $value['stom_xml_lm_dr']);
        }
        return $result;
    }

    /*
     * Данный метод, будет возвращать те случаи из XML где:
     * 1) Более одного первичного случая
     * 2) Нет не единого первичного случая, только поторные
     */
    public function findIncorrectServices(){
        $sorted = $this->getSortedUsl();
        $errors = $this->getErrorsFromSorted($sorted);
        if (array_key_exists('twoOrMorePrimary', $errors)){
            $twoOrMore = $this->getTwoOrMorePrimaryError($errors);
        }
        else{
            $twoOrMore = [];
        }
        if (array_key_exists('haveNoPrimary', $errors)){
            $haveNoPrimary = $this->getHaveNoPrimary($errors);
        }
        else {
            $haveNoPrimary  = [];
        }
        return ['TwoOrMore' => $twoOrMore, 'HaveNoPrimary' => $haveNoPrimary];
    }

}