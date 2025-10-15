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
        '19Z01.22', '19Z01.23'
    ];
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

}