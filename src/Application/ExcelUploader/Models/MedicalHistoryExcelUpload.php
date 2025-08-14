<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MedicalHistoryExcelUpload
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    /**
     * Функция првоеряет файл на корректность структуры, если файл не подходит по структуре с заданной схемой,
     * вернет ошибку.
     * Структура определяется заголовком, если заголовок совпадает с заданной схемой, все отлично
     * @return void
     */
    private function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = ['Номер ИБ', 'Пациент', 'Серия/номер полиса', 'Дата поступления', 'Дата выписки', 'Отделение', 'Лечащий врач'];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    private function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[0] = str_replace("\\", "/", $case[0]);
            $case[3] = strtotime($case[3]);
            if($case[4] === null){
                $case[4] = null;
            }
            else {
                $case[4] = strtotime($case[4]);
            }
            $formattedCases[] = $case;
        }
        return $formattedCases;
    }
    /**
     * Метод считывает файл Excel в массив данных, для дальнейшей обработки
     * @return array
     */
    private function readExcel($file) : array{
        $excelData = [];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        //Получить номер последней строки
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
            $excelData['excelTableHeader'] = $excelTableHeader;
            $excelData['excelRows'] = $this->formatCases($rows);
        }
        return $excelData;
    }

    /**
     * Метод вставляет прочитанные из Excel файл с ИБ данные в БД
     * @param $file
     * @return bool
     */
    public function excelDataToMySQLData ($file) : bool{
        $excelData = $this->readExcel($file);
        //пишем SQL запрос
        $query = ("INSERT INTO medical_histories (medical_history_number, medical_history_patient, medical_history_insurance_policy,
                   medical_history_date_in, medical_history_date_out, medical_history_hospital_department, medical_history_doctor ) VALUES");
        foreach ($excelData['excelRows'] AS $row) {
            $row[4] = $row[4] ?? 0;
            $query .= (" ('$row[0]', '$row[1]', '$row[2]', $row[3],  $row[4], '$row[5]', '$row[6]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return true;
    }

    public function getMedicalHistories(){
        $query = ("SELECT * FROM medical_histories");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

}