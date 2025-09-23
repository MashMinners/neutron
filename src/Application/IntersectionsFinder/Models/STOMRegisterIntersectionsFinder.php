<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Models;

use Engine\Database\IConnector;

class STOMRegisterIntersectionsFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function dateConvert($intersections){
        foreach ($intersections AS $intersection){
            $intersection['stom_register_treatment_start'] = date('d.m.Y', $intersection['stom_register_treatment_start']);
            $intersection['stom_register_treatment_end'] = date('d.m.Y', $intersection['stom_register_treatment_end']);
            $intersection['visit_date'] = date('d.m.Y', $intersection['visit_date']);
            $result[] = $intersection;
        }
        return $result;
    }

    private function findIntersections(){
        $query = ("SELECT visits.stom_visits_patient, register.stom_register_treatment_start, register.stom_register_treatment_end, register.stom_register_diagnosis, register.stom_register_doctor, Max(visits.stom_visits_date_of_visit) AS visit_date
                   FROM stom_visits AS visits
                   INNER JOIN stom_register AS register ON visits.stom_visits_patient_insurance_policy = register.stom_register_patient_insurance_policy
                   GROUP BY visits.stom_visits_patient, register.stom_register_treatment_start, register.stom_register_treatment_end, register.stom_register_diagnosis, register.stom_register_doctor");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    private function badIntersections(array $intersections){
       $badIntersections = [];
       foreach ($intersections as $row){
           $dateDiff = $row['stom_register_treatment_start'] - $row['visit_date'];
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
            $dateDiff = $row['stom_register_treatment_start'] - $row['visit_date'];
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
            $dateDiffStart = $row['stom_register_treatment_start'] - $row['visit_date'];
            $dateDiffEnd = $row['stom_register_treatment_end'] - $row['visit_date'];
            if ($dateDiffStart < 2592000 AND $dateDiffEnd > 2592000) {
                $dubiousIntersections[] = $row;
            }
        }
        $result = $this->dateConvert($dubiousIntersections);
        return $result;
    }

    public function find(){
        $intersections = $this->findIntersections();
        if (!empty($intersections)){
            $result['bad'] = $this->badIntersections($intersections);
            $result['good'] = $this->goodIntersections($intersections);
            $result['dubious'] = $this->dubiousIntersections($intersections);
        }
        return $result;
    }

}