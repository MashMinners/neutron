<?php

namespace Application\Invoices\STOM\Models;

use Engine\Database\IConnector;

class IncorrectPurposeFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

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

}