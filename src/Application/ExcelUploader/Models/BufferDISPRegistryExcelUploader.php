<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BufferDISPRegistryExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    protected function validateExcelSchema(array $excelTableHeader) : bool {
        $workSchema = ['Номер записи в реестре случаев',
                       'Ф.И.О.',
                       'Дата рождения',
                       'Полис',
                       'Дата начала лечения',
                       'Дата окончания лечения',
                       'Диагноз основной',
                       'ФИО врача',
                       'Признак диспансеризации',
                       'Уникальный код случая',
                       'PURP'
        ];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    protected function formatCases (array $cases) : array{
        foreach ($cases as $case){
            $case[1] = strtotime($case[1]);
            $case[3] = strtotime($case[3]);
            $case[4] = strtotime($case[4]);
            $formattedCases[$case[8]] = $case;
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

    public function excelDataToMySQLData ($file) {
        //Получаем данные из Excel
        $excelData = $this->readExcel($file);
        //пишем SQL запрос, в зависимости от типа реестра
        $query = ("INSERT INTO buffer_disp_register (buffer_disp_register_patient, buffer_disp_register_patient_date_birth, 
                               buffer_disp_register_patient_insurance_policy, buffer_disp_register_treatment_start, buffer_disp_register_treatment_end, 
                               buffer_disp_register_diagnosis, buffer_disp_register_doctor, buffer_disp_register_disp_sign, 
                               buffer_disp_register_unique_entry, buffer_disp_register_purpose) VALUES");
        foreach ($excelData AS $row) {
            $query .= (" ('$row[0]', '$row[1]', '$row[2]', $row[3],  $row[4], '$row[5]', '$row[6]', '$row[7]', '$row[8]', '$row[9]'),");
        };
        //Вставляем данные в БД
        $query = substr($query,0,-1);
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result['inserted'] = 'Вставлено записей по диспансеризации '.count($excelData);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE `buffer_disp_register`");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}