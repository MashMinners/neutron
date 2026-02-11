<?php

namespace Application\Invoices\Uploader\STOM\Models;

use Application\XlsParser\ExcelParser;
use Engine\Database\IConnector;
use function DI\string;

class ExcelUploader extends ExcelParser
{
    private  $workSchema = ['Номер записи в реестре случаев',
        'Ф.И.О.',
        'Дата рождения',
        'Полис',
        'Дата начала лечения',
        'Дата окончания лечения',
        'Диагноз основной',
        'ФИО врача',
        'Амб.талон',
        'Уникальный код случая',
        'Цель посещения'
    ];
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    /**
     * Вставляем данные в БД
     * @param array $excelData
     * @return array
     */
    public function excelDataToMySQLData(string $file){
        $excelData = $this->readExcel($file);
        $excelTableHeader = array_shift($excelData);
        $excelFieldsKeys = $this->getExcelFieldsKeys($this->workSchema, $excelTableHeader);
        $query = ("INSERT INTO buffer_stom_register (buffer_stom_register_patient, buffer_stom_register_patient_date_birth, 
                               buffer_stom_register_patient_insurance_policy, buffer_stom_register_treatment_start, buffer_stom_register_treatment_end, 
                               buffer_stom_register_diagnosis, buffer_stom_register_doctor, buffer_stom_register_ambulatory_coupon, 
                               buffer_stom_register_unique_entry, buffer_stom_register_purpose) VALUES");
        foreach ($excelData AS $row) {
            $fio = "'".$row[$excelFieldsKeys['Ф.И.О.']]."'";
            $dateBirth = $this->formatDates($row[$excelFieldsKeys['Дата рождения']]);
            $insurancePolicy = "'".$row[$excelFieldsKeys['Полис']]."'";
            $treatmentStart = $this->formatDates($row[$excelFieldsKeys['Дата начала лечения']]);
            $treatmentEnd = $this->formatDates($row[$excelFieldsKeys['Дата окончания лечения']]);
            $diagnosis = "'".$row[$excelFieldsKeys['Диагноз основной']]."'";
            $doctor = "'".$row[$excelFieldsKeys['ФИО врача']]."'";
            $ambulatoryCoupon = "'".$row[$excelFieldsKeys['Амб.талон']]."'";
            $uniqueEntry = "'".$row[$excelFieldsKeys['Уникальный код случая']]."'";
            $purpose = "'".$row[$excelFieldsKeys['Цель посещения']]."'";
            $query .= (" ($fio, $dateBirth, $insurancePolicy, $treatmentStart,  $treatmentEnd, $diagnosis, $doctor, $ambulatoryCoupon, $uniqueEntry, $purpose),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей по стоматологии '.count($excelData);
        return $result;
    }

}