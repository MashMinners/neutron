<?php

namespace Application\InvoiceRegisterAnalyzer\Models;

use Engine\Database\IConnector;

class StomRegisterAnalyzer
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private $_withoutCode = [
        '19ALL.0', '19B00.2', '19B37.0', '19D10.1', '19D10.10', '19K03.0', '19K03.6', '19K05.0', '19L05.1', '19K05.3',
        '19L05.4', '19K07.5', '19K07.6', '19K11.2', '19K12.0', '19K12.1', '19K13.0', '19K13.2', '19K14.0', '19K14.1',
        '19K14.6', '19L43.3', '19M12.8', '19S00.5', '19S00.7', '19S01.4', '19S01.5', '19S02.6', '19Z01.2', '19Z01.21',
        '19Z01.22', '19Z01.23', '19K05.4'
    ];

    private $_withCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.0', '19K04.04', '19K04.05', '19K04.06',
        '19K04.4', '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51',
        '19K04.52', '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.2', '19K05.31', '19K05.32',
        '19K05.41', '19K06.8', '19K08.3', '19K10.2', '19K10.3'
    ];

    private $_simultaneousCode = [
        '19K02.1', '19K02.2', '19K04.01', '19K04.02', '19K04.03', '19K04.04', '19K04.05', '19K04.06', '19K04.4',
        '19K04.41', '19K04.42', '19K04.43', '19K04.44', '19K04.45', '19K04.46', '19K04.5', '19K04.51', '19K04.52',
        '19K04.53', '19K04.54', '19K04.55', '19K04.56', '19K04.8', '19K05.31', '19K05.41', '19K10.2', '19K10.3'
    ];

    private function wrapWithSingleQuotes(array $data){
        $entries = '';
        foreach ($data as $row){
            $entries .= "'".$row."'," ;
        }
        $entries = substr($entries,0,-1);
        return $entries;
    }

    /**
     * Находит некоректные цели в случаях по стоматологии
     * 1.0 там где 1 визит
     * 3.0 там где 2+ визитов.
     * Если в XML это не так метод вернет случаи как некорректные цели
     * @return array|false
     */
    public function findIncorrectPurpose(){
        /**
         * Задача выбрать те записи где больше одной услуги но цель стоит 1.0, а так же те услуги,
         * где услуга одна, но цель стоит 3.0, т.е. как для множественного оказания
         */
        /*$query = ("SELECT * FROM stom_xml_hm_zsl_sl
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase =  stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_hm_zsl_sl_usl ON stom_xml_hm_zsl_sl_usl_sl_id = stom_xml_hm_zsl_sl_sl_id
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                   INNER JOIN stom_xml_pm_sl ON stom_xml_pm_sl_idcase = stom_xml_hm_zsl_idcase
                   WHERE stom_xml_hm_zsl_sl_usl_count > 1 AND stom_xml_hm_zsl_sl_p_cel = '1.0'
                   OR stom_xml_hm_zsl_sl_usl_count = 1 AND stom_xml_hm_zsl_sl_p_cel = '3.0'");*/
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase =  stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_hm_zsl_sl_usl ON stom_xml_hm_zsl_sl_usl_sl_id = stom_xml_hm_zsl_sl_sl_id
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                   WHERE stom_xml_hm_zsl_sl_usl_count > 1 AND stom_xml_hm_zsl_sl_p_cel = '1.0' 
                   OR stom_xml_hm_zsl_sl_usl_count = 1 AND stom_xml_hm_zsl_sl_p_cel = '3.0'");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Вернет те случаи, где проставлен зуб для списка диагнозов
     * Если зуб для этого перечня диагнозов проставлен, то это ошибка, фонд откажет
     * @return array
     */
    public function findIncorrectZub(){
        $query = ("SELECT * FROM stom_xml_pm_sl_stom
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase = stom_xml_pm_sl_stom_sl_idcase
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                    ");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $withoutCodes = [];
        foreach ($result AS $single){
            if ($single['stom_xml_pm_sl_stom_zub'] !== ''){
                if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $this->_withoutCode)){
                    $withoutCodes[] = $single;
                }
            }

        }
        return $withoutCodes;

    }

    /**
     * Вернет те случаи, где для списка диагнозов зуб не проставлен.
     * Это являтся ошибкой, так как для этих диагнозов обязательно указания кода зуба
     * @return array
     */
    public function findRequiredZubCode(){
        $query = ("SELECT * FROM stom_xml_pm_sl_stom
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase = stom_xml_pm_sl_stom_sl_idcase
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac
                    ");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $withCodes = [];
        foreach ($result AS $single){
            if ($single['stom_xml_pm_sl_stom_zub'] === ''){
                if (in_array($single['stom_xml_pm_sl_stom_code_usl'], $this->_withCode)){
                    $withCodes[] = $single;
                }
            }
        }
        return $withCodes;
    }

    /**
     * Находит случаи где на один зуб! установленны диагнозы из списка
     * Так быть не должно. На один зуб может быть установлен лишь один диагноз из списка иначе фонд откажет
     * @return array|false
     */
    public function findSimultaneousZubInclusion(){
        /*$query = ("SELECT * FROM stom_xml_pm_sl_stom
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_idcase = stom_xml_pm_sl_stom_sl_idcase
                   INNER JOIN stom_xml_lm ON stom_xml_lm__id_pac = stom_xml_hm_zsl_id_pac   
                   WHERE stom_xml_pm_sl_stom_idstom > 1");*/
        $query = ("SELECT stom_xml_pm_sl_stom_sl_id FROM stom_xml_pm_sl_stom
                   WHERE stom_xml_pm_sl_stom_idstom > 1");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while ($result = $stmt->fetch()){
            $IDs[] = "'".$result['stom_xml_pm_sl_stom_sl_id']."'";
        }
        $IDs = implode(', ', $IDs);
        $query = ("SELECT * FROM stom_xml_pm_sl_stom WHERE stom_xml_pm_sl_stom_sl_id IN ($IDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $newResult = [];
        while ($result = $stmt->fetch()){
            $newResult[$result['stom_xml_pm_sl_stom_sl_id']][$result['stom_xml_pm_sl_stom_idstom']]['stom_xml_pm_sl_stom_code_usl'] = $result['stom_xml_pm_sl_stom_code_usl'];
            $newResult[$result['stom_xml_pm_sl_stom_sl_id']][$result['stom_xml_pm_sl_stom_idstom']]['stom_xml_pm_sl_stom_zub']  = $result['stom_xml_pm_sl_stom_zub'];
        }
        //Проработать этот момент. Нужно найти одновременное включение в случай лечения одного зуба УЕТОВ из
        //$_simultaneousCode
        $simultaneous = [];
        foreach ($newResult AS $id => $single){
            $zub = [];
            $diagnosis = [];
            foreach ($single as $key => $value) {
                if (in_array($value['stom_xml_pm_sl_stom_code_usl'], $this->_simultaneousCode) AND $value['stom_xml_pm_sl_stom_zub'] !==''){
                    //$buffer[$key]['id'] = $key;
                    //$buffer[$key]['zub'] = $value['stom_xml_pm_sl_stom_zub'];
                    $zub[$key] = $value['stom_xml_pm_sl_stom_zub'];
                    $diagnosis[$key] = $value['stom_xml_pm_sl_stom_code_usl'];
                }
            }
            if (count($zub) > count(array_unique($zub))){
                $simultaneous[] = $id;
            }
        }
        $slIDs = '';
        foreach ($simultaneous as $single){
            $slIDs .= "'".$single."'".', ';
        }
        $slIDs = substr($slIDs,0,-2);
        $query = ("SELECT * FROM stom_xml_lm
                   INNER JOIN stom_xml_hm_zsl ON stom_xml_hm_zsl_id_pac = stom_xml_lm__id_pac
                   INNER JOIN stom_xml_pm_sl_stom ON stom_xml_pm_sl_stom_sl_idcase = stom_xml_hm_zsl_idcase
                   WHERE stom_xml_pm_sl_stom_sl_id IN ($slIDs)");

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;

    }

    /*
     * Данный метод, будет возвращать те случаи из XML где:
     * 1) Более одного первичного случая
     * 2) Нет не единого первичного случая, только поторные
     */
    public function findIncorrectUSL(){
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
        /**
         * Проверяю количество первичных приемов, если более чем 1 то случай ошибка
         * Если B01.065.003 вообще нет то это тоже ошибка
         */
        $errors = [];
        foreach ($sortedArray AS $key => $value){
            if (!array_key_exists('B01.065.003',$value)){
                $errors['haveNoPrimary'][] = $key;
            }else{
                $B01Count = count($value['B01.065.003']);
                if ($B01Count > 1) {
                    $errors['twoOrMorePrimary'][] = $key;
                }
            }
        }
        //Найти ФИО тех пациентов у которых найдены ошибки по 2 и более первичным услугам
        $slIDs = $this->wrapWithSingleQuotes($errors['twoOrMorePrimary']);
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl AS SL
                   INNER JOIN stom_xml_hm_zsl AS ZSL ON stom_xml_hm_zsl_idcase = SL.stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_lm AS LM ON stom_xml_lm__id_pac = ZSL.stom_xml_hm_zsl_id_pac
                   WHERE stom_xml_hm_zsl_sl_sl_id IN ($slIDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        //Найти ФИО тех пациентов у которых найдены ошибки по отсуствующей услуге B01.065.003
        $slIDs = $this->wrapWithSingleQuotes($errors['haveNoPrimary']);
        $query = ("SELECT * FROM stom_xml_hm_zsl_sl AS SL
                   INNER JOIN stom_xml_hm_zsl AS ZSL ON stom_xml_hm_zsl_idcase = SL.stom_xml_hm_zsl_sl_idcase
                   INNER JOIN stom_xml_lm AS LM ON stom_xml_lm__id_pac = ZSL.stom_xml_hm_zsl_id_pac
                   WHERE stom_xml_hm_zsl_sl_sl_id IN ($slIDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

}