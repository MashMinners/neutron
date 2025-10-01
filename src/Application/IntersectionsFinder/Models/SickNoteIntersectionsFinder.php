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
            $intersection['medical_history_date_in'] = date('d.m.Y H:i', $intersection['medical_history_date_in']);
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
                          sick_note_is_closed, medical_history_date_in, medical_history_date_out
                   FROM sick_notes
                   INNER JOIN medical_histories ON medical_history_patient = sick_note_patient");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Больничный лист открыт при закрытой ИБ
     * @param array $sickNotes
     * @return array
     */
    private function filterSNOpenMHClosed(array $sickNotes){
        $attentionNotes = [];
        foreach ($sickNotes as $note){
            if($note['sick_note_closed_date'] === 0 AND $note['medical_history_date_out'] !== 0){
                $attentionNotes[] = $note;
            }
        }
        return $attentionNotes;
    }

    /**
     * Больничный лист закрыт позднее закрытия истории болезни
     * Больничный лист закрыт когда история болезни открыта
     * @param array $sickNotes
     * @return array
     */
    private function filterMHClosedSNOpen(array $sickNotes){
        $dateDiffNotes = [];
        foreach ($sickNotes as $note){
            $a = date('d.m.Y', $note['medical_history_date_out']);
            $dateOut = strtotime($a);
            $dateClose = $note['sick_note_closed_date'];
            $dateDiff = $dateClose - $dateOut;
            if ($dateDiff >= 86400){
                $dateDiffNotes[] = $note;
            }
        }
        return $dateDiffNotes;
    }

    /**
     * Больничный лист открыт позже даты госпитализации
     * @param array $sickNotes
     * @return array
     */
    private function filterDateDiff(array $sickNotes){
        $dateDiffNotes = [];
        foreach ($sickNotes as $note){
            $a = date('d.m.Y', $note['medical_history_date_in']);
            $b = strtotime($a);
            $dateDiff2 = $note['sick_note_open_date'] - $b;
            if ($dateDiff2 >= 86400){
                $dateDiffNotes[] = $note;
            }

        }
        return $dateDiffNotes;
    }

    /**
     * Поиск открытых историй болезни
     * @param array $sickNotes
     * @return array
     */
    private function filterOpenedMH(array $sickNotes){
        $openedMH = [];
        foreach ($sickNotes as $note){
            if($note['medical_history_date_out'] === 0){
                $openedMH[] = $note;
            }
        }
        return $openedMH;
    }

    public function find(){
        $intersections = $this->findHospitalIntersections();
        $attentionNotes = $this->filterSNOpenMHClosed($intersections);
        $filteredByDateDiff = $this->filterDateDiff($intersections);
        $openedMH = $this->filterOpenedMH($intersections);
        $dateDiff = $this->filterDateDiff($intersections);
        //$result['dubious'] = $this->dateConvert($dubious);
        $result = $this->filterMHClosedSNOpen($intersections);
        return $this->dateConvert($dateDiff);
    }

}