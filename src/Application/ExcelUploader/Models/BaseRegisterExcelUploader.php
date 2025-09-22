<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BaseRegisterExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    protected function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = ['Номер записи в реестре случаев', 'Ф.И.О.', 'Дата рождения', 'Полис', 'Дата начала лечения', 'Дата окончания лечения', 'Диагноз основной', 'ФИО врача'];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    protected function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[2] = strtotime($case[2]);
            $case[4] = strtotime($case[4]);
            $case[5] = strtotime($case[5]);
            $formattedCases[$case[0]] = $case;
        }
        return $formattedCases;
    }

    protected function readExcel($file) : array{
        $excelData = [];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        unset($rows[0]);
        //Получить заголовки таблицы
        $excelTableHeader = array_shift($rows);
        $isCorrect = $this->validateExcelSchema($excelTableHeader);
        if ($isCorrect){
            $excelData = $this->formatCases($rows);
        }
        return $excelData;
    }

    protected function getUniqueEntries(array $excelData){
        $entries = '';
        foreach ($excelData as $row){
            $entries .= $row[0].', ';
        }
        $entries = substr($entries,0,-2);
        return $entries;
    }

    protected function removeDuplicatesFromDatabase(array $duplicates){
        $duplicates = implode(', ', $duplicates);
        $query = ("DELETE FROM $this->_table WHERE $this->_unique_entry IN ($duplicates)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

    protected function findEntryDuplicatesInDatabase(string $uniqueEntries){
        $duplicates = [];
        $query = ("SELECT $this->_unique_entry FROM $this->_table
                   WHERE $this->_unique_entry IN ($uniqueEntries)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $key=>$value){
            $duplicates[] = $value['dp_register_unique_entry'];
        }
        return $duplicates;
    }

    public function excelDataToMySQLData ($file) {
        //Получаем данные из Excel
        $excelData = $this->readExcel($file);
        //получаю уникальный идентификатор записи в реестре счетов
        $uniqueEntries = $this->getUniqueEntries($excelData);
        //Ищу дубликаты этих записей в базе данных
        $duplicates = $this->findEntryDuplicatesInDatabase($uniqueEntries);
        //Удаляю дубликаты из БД, на основании той мысли, что эти данные устарели
        if (!empty($duplicates)){
            $this->removeDuplicatesFromDatabase($duplicates);
            $result['deleted'] = 'Удалено записей '.count($duplicates);
        }
        //пишем SQL запрос, в зависимости от типа реестра
        $query = ("INSERT INTO $this->_table ($this->_unique_entry, $this->_register_patient, $this->_register_patient_date_birth, 
                               $this->_register_patient_insurance_policy, $this->_register_treatment_start, $this->_register_treatment_end, 
                               $this->_register_diagnosis, $this->_register_doctor) VALUES");
        foreach ($excelData AS $row) {
            $query .= (" ('$row[0]', '$row[1]', '$row[2]', $row[3],  $row[4], '$row[5]', '$row[6]', '$row[7]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей '.count($excelData);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE $this->_table");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}