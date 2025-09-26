<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MedicalHistoriesExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = ['Номер ИБ', 'Пациент', 'Дата рождения', 'Возраст', 'Серия/номер полиса', 'Дата поступления', 'Дата выписки', 'Отделение', 'Лечащий врач'];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    private function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[0] = str_replace("\\", "/", $case[0]);
            $case[2] = strtotime($case[2]);
            $case[5] = strtotime($case[5]);
            if($case[6] === null){
                $case[6] = 0;
            }
            else {
                $case[6] = strtotime($case[6]);
            }
            $formattedCases[$case[0]] = $case;
        }
        return $formattedCases;
    }

    private function readExcel($file) : array{
        $excelData = [];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        unset($rows[0]);
        unset($rows[$highestRow-1]);
        //Получить заголовки таблицы
        $excelTableHeader = array_shift($rows);
        $isCorrect = $this->validateExcelSchema($excelTableHeader);
        if ($isCorrect){
            $excelData = $this->formatCases($rows);
        }
        return $excelData;
    }

    private function getUniqueMHs(array $excelData){
        $entries = '';
        foreach ($excelData as $row){
            $entries .= "'".$row[0]."'".', ';
        }
        $entries = substr($entries,0,-2);
        return $entries;
    }

    private function findEntryDuplicatesInDatabase(string $uniqueMHs){
        $duplicates = [];
        $query = ("SELECT medical_history_unique_number FROM medical_histories
                   WHERE medical_histories.medical_history_unique_number IN ($uniqueMHs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $key=>$value){
            $duplicates[] = $value['medical_history_unique_number'];
        }
        return $duplicates;
    }

    private function removeDuplicatesFromDatabase(array $duplicates){
        $duplicatesString = '';
        foreach ($duplicates as $duplicate){
            $duplicatesString .= "'".$duplicate."'".', ';
        }
        $duplicatesString = substr($duplicatesString,0,-2);
        $query = ("DELETE FROM medical_histories WHERE medical_histories.medical_history_unique_number IN ($duplicatesString)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

    public function excelDataToMySQLData ($file) {
        $excelData = $this->readExcel($file);
        $uniqueMHs = $this->getUniqueMHs($excelData);
        $duplicates = $this->findEntryDuplicatesInDatabase($uniqueMHs);
        if (!empty($duplicates)){
            $this->removeDuplicatesFromDatabase($duplicates);
            $result['deleted'] = 'Удалено записей '.count($duplicates);
        }
        $query = ("INSERT INTO medical_histories (medical_history_unique_number, medical_history_patient,
                               medical_history_patient_date_birth, medical_history_patient_age, medical_history_insurance_policy, 
                               medical_history_date_in, medical_history_date_out, medical_history_hospital_department, medical_history_doctor) 
                   VALUES");
        foreach ($excelData AS $row) {
            $query .= (" ('$row[0]', '$row[1]', $row[2], $row[3],  '$row[4]', $row[5], $row[6], '$row[7]', '$row[8]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей '.count($excelData);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE `medical_histories`");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}