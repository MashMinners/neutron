<?php

namespace Application\IntersectionsFinder\Models;

use Engine\Database\IConnector;

class SickNoteIntersectionsFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function dateConvert(array $intersections){
        $result = [];
        foreach ($intersections AS $intersection){
            $intersection['sick_note_patient_date_birth'] = date('d.m.Y', $intersection['sick_note_patient_date_birth']);
            $intersection['sick_note_open_date'] = date('d.m.Y', $intersection['sick_note_open_date']);
            if ($intersection['sick_note_closed_date'] === 0){
                $intersection['sick_note_closed_date'] = 'ЛН открыт';
            }
            else{
                $intersection['sick_note_closed_date'] = date('d.m.Y', $intersection['sick_note_closed_date']);
            }
            if ($intersection['medical_history_date_out'] === 0){
                $intersection['medical_history_date_out'] = 'ИБ открыта';
            }else{
                $intersection['medical_history_date_out'] = date('d.m.Y', $intersection['medical_history_date_out']);
            }
            $result[] = $intersection;
        }
        return $result;
    }

    private function findHospitalIntersections(){
        $query = ("SELECT sick_note_unique_id, sick_note_type, sick_note_patient, sick_note_patient_date_birth, 
                          sick_note_issuing_doctor, sick_note_closed_doctor, sick_note_open_date, sick_note_closed_date, sick_note_days_count, 
                          sick_note_is_closed, medical_history_date_out
                   FROM sick_notes
                   INNER JOIN medical_histories ON medical_history_patient = sick_note_patient");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function find(){
        $intersections = $this->findHospitalIntersections();
        return $this->dateConvert($intersections);
    }

}