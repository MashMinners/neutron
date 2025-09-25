<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Models;

use Engine\Database\IConnector;

class DISPRegisterIntersectionsFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function dateConvert(array $intersections){
        $result = [];
        foreach ($intersections AS $intersection){
            $intersection['buffer_register_treatment_start'] = date('d.m.Y', $intersection['buffer_register_treatment_start']);
            $intersection['buffer_register_treatment_end'] = date('d.m.Y', $intersection['buffer_register_treatment_end']);
            $intersection['medical_history_date_in'] = date('d.m.Y', $intersection['medical_history_date_in']);
            $intersection['medical_history_date_out'] = date('d.m.Y', $intersection['medical_history_date_out']);
            $result[] = $intersection;
        }
        return $result;
    }

    private function findIntersections(){
        $query = ("SELECT register.buffer_register_patient, register.buffer_register_treatment_start, register.buffer_register_treatment_end,
                          register.buffer_register_diagnosis, register.buffer_register_doctor, histories.medical_history_date_in, histories.medical_history_date_out  
                   FROM medical_histories AS histories
                   INNER JOIN buffer_register as register ON histories.medical_history_insurance_policy = register.buffer_register_patient_insurance_policy");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    private function badIntersections(array $intersections){
        $badIntersections = [];

        foreach ($intersections as $row){
            /*
             * medical_history_date_in = 01.08.2025 08:52:00 но в секундах
             * dp_register_treatment_start = 01.08.2025 тоже в секундах
             * Если дата поступления и дата открытия карты ДП одна то первое условие выполнится, так как в один день
             * чистая дата dp_register_treatment_start в любом случае будет меньше даты medical_history_date_in + секунды
             *
             * medical_history_date_in = 01.08.2025
             * dp_register_treatment_start = 02.08.2025 дата открытия ДП между датами открытия ИБ и закрытия ИБ
             * medical_history_date_out = 05.08.2025
             * dp_register_treatment_end = 06.08.2025
             *
             * dp_register_treatment_start = 01.08.2025
             * medical_history_date_in = 01.08.2025 08:52:00 // дата ИБ между датами открытия ДП и закрытия ДП
             * dp_register_treatment_end = 06.08.2025
             * medical_history_date_out = 07.08.2025
             *
             * Обрати внимание на то, что medical_history даты имеют +++ в секундах от обычной даты! Это может давать неточность
             * в двух сравнениях а именно в $row['medical_history_date_in'] < $row['dp_register_treatment_end']
             * и ($row['dp_register_treatment_start'] >= $row['medical_history_date_in']
             */
            //Если медицинская история болезни ОТКРЫТА во время того как была сделана диспансеризация
            if ($row['medical_history_date_in'] >= $row['buffer_register_treatment_start'] AND $row['medical_history_date_in'] < $row['buffer_register_treatment_end']) {
                $badIntersections[] = $row;
            }
            //Если медицинская история болезни ЗАКРЫТА во время того как была сделана диспансеризация
            if ($row['medical_history_date_out'] >= $row['buffer_register_treatment_start'] AND $row['medical_history_date_out'] < $row['buffer_register_treatment_end']) {
                $badIntersections[] = $row;
            }
            //Если карта диспансеризации ОТКРЫТА во время истории болезни
            if ($row['buffer_register_treatment_start'] >= $row['medical_history_date_in'] AND $row['buffer_register_treatment_start'] < $row['medical_history_date_out']){
                $badIntersections[] = $row;
            }
            //Если карта диспансеризации ЗАКРЫТА во время истории болезни
            if ($row['buffer_register_treatment_end'] >= $row['medical_history_date_in'] AND $row['buffer_register_treatment_end'] < $row['medical_history_date_out']){
                $badIntersections[] = $row;
            }
        }
        return $badIntersections;
    }

    public function find(){
        /**
         * В ДП попадают только закрытые карты ибо эта карта берется из реестра
         * Карта ДП открыта 01.07... сегодня 18.09
         * История болезни с 15.08 по 25.08
         * Карта ДП закрывается 20.09. Попадает в реестр за сентябрь
         * В таблице БД хрантится запись об истории болезни с 15.08 по 25.08
         * Когда будет делатся поиск пересечений, он делается по всей таблице ИБ вне зависимости от месяца, поиск вернет
         * пересечение по данной карте открытой 01.07-20.09 с ИБ 15.08-25.08
         */
        $intersections = $this->findIntersections();
        $result = $this->badIntersections($intersections);
        return $this->dateConvert($result);
    }

}