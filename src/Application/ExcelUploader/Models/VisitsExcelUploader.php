<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VisitsExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = ['ID', 'Врач', 'Пациент', 'Дата рождения', 'Статус услуги', 'Полис ОМС', 'Дата'];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    private function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[3] = strtotime($case[3]);
            $case[6] = strtotime($case[6]);
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

    private function getUniqueIDs(array $excelData){
        $entries = '';
        foreach ($excelData as $row){
            $entries .= $row[0].', ';
        }
        $entries = substr($entries,0,-2);
        return $entries;
    }

    private function findEntryDuplicatesInDatabase(string $uniqueIDs){
        $duplicates = [];
        $query = ("SELECT visits_unique_id FROM visits
                   WHERE visits_unique_id IN ($uniqueIDs)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $key=>$value){
            $duplicates[] = $value['stom_visits_unique_id'];
        }
        return $duplicates;
    }

    private function removeDuplicatesFromDatabase(array $duplicates){
        $duplicates = implode(', ', $duplicates);
        $query = ("DELETE FROM stom_visits WHERE stom_visits.stom_visits_unique_id IN ($duplicates)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

    public function excelDataToMySQLData ($file) {
        //Получаем данные из Excel
        $excelData = $this->readExcel($file);
        //получаю уникальный идентификатор записи в реестре счетов
        $uniqueIDs = $this->getUniqueIDs($excelData);
        //Ищу дубликаты этих записей в базе данных
        $duplicates = $this->findEntryDuplicatesInDatabase($uniqueIDs);
        //Удаляю дубликаты из БД, на основании той мысли, что эти данные устарели
        if (!empty($duplicates)){
            $this->removeDuplicatesFromDatabase($duplicates);
            $result['deleted'] = 'Удалено записей '.count($duplicates);
        }
        //пишем SQL запрос, в зависимости от типа реестра
        $query = ("INSERT INTO visits (visits_unique_id, visits_doctor, visits_patient, 
                               visits_patient_date_birth, visits_service_status, visits_patient_insurance_policy, 
                               visits_date_of_visit) 
                   VALUES");
        foreach ($excelData AS $row) {
            $query .= (" ('$row[0]', '$row[1]', '$row[2]', $row[3],  '$row[4]', '$row[5]', '$row[6]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей '.count($excelData);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE `stom_visits`");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}