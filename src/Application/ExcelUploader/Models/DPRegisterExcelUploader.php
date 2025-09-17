<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DPRegisterExcelUploader extends BaseRegisterExcelUploader
{
    private function findEntryDuplicatesInDatabase(string $uniqueEntries){
        $duplicates = [];
        $query = ("SELECT dp_register_unique_entry FROM dp_register
                   WHERE dp_register_unique_entry IN ($uniqueEntries)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $key=>$value){
            $duplicates[] = $value['dp_register_unique_entry'];
        }
        return $duplicates;
    }

    private function removeDuplicatesFromDatabase(array $duplicates){
        $duplicates = implode(', ', $duplicates);
        $query = ("DELETE FROM dp_register WHERE dp_register_unique_entry IN ($duplicates)");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
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
        $query = ("INSERT INTO dp_register (dp_register_unique_entry, dp_register_patient, dp_register_patient_date_birth, 
                               dp_register_patient_insurance_policy, dp_register_treatment_start, dp_register_treatment_end, 
                               dp_register_diagnosis, dp_register_doctor) VALUES");
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

}