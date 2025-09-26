<?php

namespace Application\Registry\Models;

use Engine\Database\IConnector;

class STOMRegistry
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }
    public function findDuplicates(){
        //SELECT [1].[Полис], [1].[Номер записи в реестре случаев], [1].[Ф#И#О#], [1].[Дата рождения], [1].[Дата начала лечения], [1].[Дата окончания лечения], [1].[Диагноз основной], [1].[ФИО врача]
        //FROM 1
        //WHERE ((([1].[Полис]) In (SELECT [Полис] FROM [1] As Tmp GROUP BY [Полис] HAVING Count(*)>1 )))
        //ORDER BY [1].[Полис];
        $query = ("SELECT buffer_stom_register_unique_entry, buffer_stom_register_patient, buffer_stom_register_patient_date_birth, 
                          buffer_stom_register_patient_insurance_policy, buffer_stom_register_treatment_start, buffer_stom_register_treatment_end, 
                          buffer_stom_register_diagnosis, buffer_stom_register_doctor  
                   FROM buffer_stom_register
                   WHERE buffer_stom_register_patient_insurance_policy 
                   IN (SELECT buffer_stom_register_patient_insurance_policy FROM buffer_stom_register AS TMP
                       GROUP BY buffer_stom_register_patient_insurance_policy HAVING COUNT(*)>1)
                   ORDER BY buffer_stom_register_patient_insurance_policy");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

}