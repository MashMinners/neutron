<?php

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SickNoteExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = [
            'Номер листка', 'Тип', 'Дубл.', 'Пациент', 'Листок выдан', 'Выдавший врач', 'Должность выдавшего врача',
            'Закрывший врач', 'Должность закрывшего врача', 'С какого числа', 'По какое число', 'Дата закрытия',
            'Приступить к работе', 'Выдан в нашей МО', 'Тип ЛН', 'Статус ЭЛН в СФР', 'Статус подписи'
        ];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    private function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[9] = strtotime($case[9]);
            $case[10] = strtotime($case[10]);
            $case[11] = strtotime($case[11]);
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
        $query = ("SELECT sick_note_unique_id FROM sick_notes
                   WHERE sick_note_unique_id IN ($uniqueIDs)");
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
        $query = ("DELETE FROM sick_notes WHERE sick_note_unique_id IN ($duplicates)");
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
        $query = ("INSERT INTO sick_notes (sick_note_unique_id, sick_note_type, sick_note_patient, issuing_doctor,
                        closed_doctor, sick_note_open_opent_date, sick_note_finish_date, sick_note_closed_date, sick_note_get_to_work)
                        
                   VALUES");
        foreach ($excelData AS $row) {
            $query .= (" ('$row[0]', '$row[1]', '$row[3]', $row[5],  '$row[7]', '$row[9]', '$row[10]', '$row[11]', '$row[12]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей '.count($excelData);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE `sick_notes`");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}