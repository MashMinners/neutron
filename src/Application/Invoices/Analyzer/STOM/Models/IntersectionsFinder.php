<?php

namespace Application\Invoices\Analyzer\STOM\Models;

use Engine\Database\IConnector;

class IntersectionsFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function dateConvert($intersections){
        $result = [];
        foreach ($intersections AS $intersection){
            $intersection['buffer_stom_register_treatment_start'] = date('d.m.Y', $intersection['buffer_stom_register_treatment_start']);
            $intersection['buffer_stom_register_treatment_end'] = date('d.m.Y', $intersection['buffer_stom_register_treatment_end']);
            $intersection['visit_date'] = date('d.m.Y', $intersection['visit_date']);
            $result[] = $intersection;
        }
        return $result;
    }

    /**
     * Получает все пересечения одним массивом
     * @return array|false
     */
    private function findIntersections(){
        $query = ("SELECT visits_patient, visits_patient_insurance_policy, register.buffer_stom_register_treatment_start, 
                          register.buffer_stom_register_treatment_end, register.buffer_stom_register_diagnosis, register.buffer_stom_register_doctor,
                          register.buffer_stom_register_purpose, Max(visits_date_of_visit) AS visit_date
                   FROM visits
                   INNER JOIN buffer_stom_register AS register ON visits_patient_insurance_policy = register.buffer_stom_register_patient_insurance_policy
                   GROUP BY visits_patient, visits_patient_insurance_policy, register.buffer_stom_register_treatment_start, 
                            register.buffer_stom_register_treatment_end, register.buffer_stom_register_diagnosis, register.buffer_stom_register_doctor,
                            register.buffer_stom_register_purpose");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    private function badIntersections(array $intersections){
        $badIntersections = [];
        foreach ($intersections as $row){
            $dateDiff = $row['buffer_stom_register_treatment_start'] - $row['visit_date'];
            if ($dateDiff < 2592000) {
                $badIntersections[] = $row;
            }
        }
        $result = $this->dateConvert($badIntersections);
        return $result;
    }

    private function goodIntersections(array $intersections){
        $goodIntersections = [];
        foreach ($intersections as $row){
            $dateDiff = $row['buffer_stom_register_treatment_start'] - $row['visit_date'];
            if ($dateDiff > 2592000) {
                $goodIntersections[] = $row;
            }
        }
        $result = $this->dateConvert($goodIntersections);
        return $result;
    }

    private function dubiousIntersections(array $intersections){
        $dubiousIntersections = [];
        foreach ($intersections as $row){
            $dateDiffStart = $row['buffer_stom_register_treatment_start'] - $row['visit_date'];
            $dateDiffEnd = $row['buffer_stom_register_treatment_end'] - $row['visit_date'];
            if ($dateDiffStart < 2592000 AND $dateDiffEnd > 2592000) {
                $dubiousIntersections[] = $row;
            }
        }
        $result = $this->dateConvert($dubiousIntersections);
        return $result;
    }

    /**
     * Сортирует пересечения по группам: плохие, хорошие, средние, чтобы удобно выводить пользователю
     * @return array
     */
    public function findSortedIntersections(){
        $result = [];
        $intersections = $this->findIntersections();
        if (!empty($intersections)){
            $result['bad'] = $this->badIntersections($intersections);
            $result['good'] = $this->goodIntersections($intersections);
            $result['dubious'] = $this->dubiousIntersections($intersections);
        }
        return $result;
    }
}