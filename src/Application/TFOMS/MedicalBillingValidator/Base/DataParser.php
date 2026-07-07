<?php

namespace Application\TFOMS\MedicalBillingValidator\Base;

use PhpOffice\PhpSpreadsheet\IOFactory;

class DataParser
{
    private string $directory = "storage/tfoms/BillingValidator/";

    public function getExcelData(){
        // Ищем файлы .ods и .xlsx
        $odsFiles = glob($this->directory . '*.ods');
        $xlsxFiles = glob($this->directory . '*.xlsx');
        // Объединяем массивы
        $files = array_merge($odsFiles, $xlsxFiles);
        $result = [];
        foreach ($files AS $file){
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $startRow = 'A2';
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $rows = $sheet->rangeToArray(
                "$startRow:$highestColumn$highestRow", // Диапазон
                NULL,                          // Значение для пустых ячеек
                TRUE,                          // Вычислять формулы
                TRUE,                          // Форматировать значения (даты, проценты)
                TRUE                           // Использовать индексы строк/столбцов в массиве
            );
            array_shift($rows);
            $result = array_merge($result, $rows);
        }
        return $result;
    }

}