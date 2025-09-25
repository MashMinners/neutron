<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Models;

use Engine\Database\IConnector;

class BufferSTOMRegistryIntersectionsFinder
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

    private function findIntersections(){
        $query = ("SELECT visits.stom_visits_patient, visits.stom_visits_patient_insurance_policy, register.buffer_stom_register_treatment_start, 
                          register.buffer_stom_register_treatment_end, register.buffer_stom_register_diagnosis, register.buffer_stom_register_doctor,
                          register.buffer_stom_register_purpose, Max(visits.stom_visits_date_of_visit) AS visit_date
                   FROM stom_visits AS visits
                   INNER JOIN buffer_stom_register AS register ON visits.stom_visits_patient_insurance_policy = register.buffer_stom_register_patient_insurance_policy
                   GROUP BY visits.stom_visits_patient, visits.stom_visits_patient_insurance_policy, register.buffer_stom_register_treatment_start, 
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

    public function find(){
        $result = [];
        $intersections = $this->findIntersections();
        if (!empty($intersections)){
            $result['bad'] = $this->badIntersections($intersections);
            $result['good'] = $this->goodIntersections($intersections);
            $result['dubious'] = $this->dubiousIntersections($intersections);
        }
        return $result;
    }

    private function getBufferRecords(){
        $query = ("SELECT * FROM buffer_stom_register");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    private function purposesDateConvert(array $intersections){
        $result = [];
        foreach ($intersections AS $intersection){
            $intersection['buffer_stom_register_patient_date_birth'] = date('d.m.Y', $intersection['buffer_stom_register_patient_date_birth']);
            $intersection['buffer_stom_register_treatment_start'] = date('d.m.Y', $intersection['buffer_stom_register_treatment_start']);
            $intersection['buffer_stom_register_treatment_end'] = date('d.m.Y', $intersection['buffer_stom_register_treatment_end']);
            $result[] = $intersection;
        }
        return $result;
    }

    private function devidePurposes(array $records){
        $purpose = [];
        foreach ($records as $record){
            if ($record['buffer_stom_register_treatment_end']-$record['buffer_stom_register_treatment_start'] === 0){
                $purpose['single'][] = $record;
            }
            else{
                $purpose['multi'][] = $record;
            }
        }
        return $purpose;
    }

    private function devideSingleVisit(array $visits){
        $result = [];
        foreach ($visits as $visit){
            if ($visit['buffer_stom_register_purpose'] === '1.0'){
                $result['correct'][] = $visit;
            }else{
                $result['incorrect'][] = $visit;
            }
        }
        return $result;
    }

    private function devideMultiVisits(array $visits){
        $result = [];
        foreach ($visits as $visit){
            if ($visit['buffer_stom_register_purpose'] === '3.0'){
                $result['correct'][] = $visit;
            }else{
                $result['incorrect'][] = $visit;
            }
        }
        return $result;
    }

    public function findIncorrectPurposeDevided(){
        $records = $this->getBufferRecords();
        $devided = $this->devidePurposes($records);
        $converted['single'] = $this->purposesDateConvert($devided['single']);
        $converted['multi'] = $this->purposesDateConvert($devided['multi']);
        $result['single'] = $this->devideSingleVisit($converted['single']);
        $result['multi'] = $this->devideMultiVisits($converted['multi']);
        return $result;
    }

}