<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Загрузка в БД записи по ПЕРВОМУ ЭТАПУ ДИСПАНСЕРИЗАЦИИ
 */
class DPRegisterExcelUploader extends BaseRegisterExcelUploader
{
    protected $_table = 'dp_register';
    protected $_unique_entry = 'dp_register_unique_entry';

    /**
     * @param $file
     * @return array
     */
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