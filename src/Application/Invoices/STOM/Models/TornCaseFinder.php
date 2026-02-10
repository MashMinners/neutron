<?php

namespace Application\Invoices\STOM\Models;

use Engine\Database\IConnector;

class TornCaseFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function dateConvert($tornCases){
        $result = [];
        foreach ($tornCases AS $tornCase){
            $tornCase['buffer_stom_register_treatment_start'] = date('d.m.Y', $tornCase['buffer_stom_register_treatment_start']);
            $tornCase['buffer_stom_register_treatment_end'] = date('d.m.Y', $tornCase['buffer_stom_register_treatment_end']);
            $tornCase['buffer_stom_register_patient_date_birth'] = date('d.m.Y',  $tornCase['buffer_stom_register_patient_date_birth']);
            $result[] = $tornCase;
        }
        return $result;
    }

    /**
     * По факту жанный метод ищет дубликаты в реестре по полису. На деле это является поиском разорванных случаев
     * по стоматологии так как повторений быть не должно
     * @return array|false
     */
    public function findTornCases(){
        $query = ("SELECT buffer_stom_register_unique_entry, buffer_stom_register_patient, buffer_stom_register_patient_date_birth, 
                          buffer_stom_register_patient_insurance_policy, buffer_stom_register_treatment_start, buffer_stom_register_treatment_end, 
                          buffer_stom_register_diagnosis, buffer_stom_register_doctor, buffer_stom_register_purpose,
                          buffer_stom_register_ambulatory_coupon  
                   FROM buffer_stom_register
                   WHERE buffer_stom_register_patient_insurance_policy 
                   IN (SELECT buffer_stom_register_patient_insurance_policy FROM buffer_stom_register AS TMP
                       GROUP BY buffer_stom_register_patient_insurance_policy HAVING COUNT(*)>1)
                   ORDER BY buffer_stom_register_patient_insurance_policy");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $result = $this->dateConvert($result);
        return $result;
    }

}