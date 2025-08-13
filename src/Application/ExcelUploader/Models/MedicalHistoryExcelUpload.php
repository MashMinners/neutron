<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Models;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function DI\string;

class MedicalHistoryExcelUpload
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    /**
     * Функция првоеряет файл на корректность структуры, если файл не подходит по структуре с заданной схемой,
     * вернет ошибку.
     * @return void
     */
    private function validateExcel(){

    }

    /**
     * Метод считывает файл Excel в массив данных, для дальнейшей обработки
     * @return array
     */
    public function readExcel() : array{
        $data = [];
        $file = $_SERVER['DOCUMENT_ROOT']."/IB.xlsx";
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        /*$columns = [];
        foreach ($sheet->getColumnIterator() as $column) {
            $columnIndex = $column->getColumnIndex(); // Get the column letter (e.g., 'A', 'B')
            $columns[] = $columnIndex;
        }
        $highestRow = $sheet->getHighestRow();
        foreach ($columns as $column){
            for ($row = 1; $row <= $highestRow; ++$row) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $plainText = $cellValue->getPlainText();
                } else {
                    $plainText = (string) $cellValue;
                }
                $data[$column][$row] = $plainText;
            }
        }*/
        $highestRow = $sheet->getHighestRow();
        $rows = $sheet->toArray();
        unset($rows[0]);
        unset($rows[$highestRow-1]);
        $title = array_shift($rows);
        $data['title'] = $title;
        $data['cases'] = $rows;

        return $data;
    }

}