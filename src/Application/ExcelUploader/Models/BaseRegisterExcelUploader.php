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

}