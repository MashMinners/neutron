<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamChildrenInvoiceMaker extends BaseInvoicesMaker
{
    private function readExcel(string $file){
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $startRow = 12;
        $highestRow = $sheet->getHighestRow();
        //Нужно определять будет и последню колонку, если она пустая удалять ее, и так до тех пор пока не придет со значением
        //Этонужно определить в отдельный метод, необходимо очищать файл перед работой от пустых строк и колонок, люы потом избержать warnings
        $highestColumn = $sheet->getHighestColumn();

        $rows = $sheet->rangeToArray(
            "A12:$highestColumn$highestRow", // Диапазон
            NULL,                          // Значение для пустых ячеек
            TRUE,                          // Вычислять формулы
            TRUE,                          // Форматировать значения (даты, проценты)
            TRUE                           // Использовать индексы строк/столбцов в массиве
        );
        $excelTableHeader = array_shift($rows);
        array_splice($rows, -6);
        $needle = [];
        $position = 1;
        foreach ($rows AS $row){
            $dates = explode(' - ', $row['M']);
            $uniqueId = $row['J'].'-'.$dates[0].'-'.$dates[1];
            $needle[$uniqueId]['position'] = $position;
            $needle[$uniqueId]['full_name'] = $row['B'];
            //$needle[$uniqueId]['gender'] = $this->gender[$row['C']];
            $needle[$uniqueId]['gender'] = $row['C'];
            $needle[$uniqueId]['date_birth'] = $row['D'];
            $needle[$uniqueId]['place_of_birth'] = 'Приморский край';
            $needle[$uniqueId]['identity_document'] = $row['F'];
            $needle[$uniqueId]['snils'] = $row['I'];
            $needle[$uniqueId]['policy'] = $row['J'];
            $needle[$uniqueId]['medical_care'] = $row['K'];
            $needle[$uniqueId]['diagnosis'] = $row['L'];
            $needle[$uniqueId]['date_start'] = $dates[0];
            $needle[$uniqueId]['date_finish'] = $dates[1];
            $needle[$uniqueId]['volumes'] = 1;
            $needle[$uniqueId]['profile'] = 68;
            $needle[$uniqueId]['speciality'] = 49;
            $needle[$uniqueId]['payment tariff'] = $row['Q'];
            $needle[$uniqueId]['cost'] = $row['R'];
            $needle[$uniqueId]['result'] = $row['S'];
            $position++;
        }
        return $needle;
    }

    public function makeInvoice(string $file){
        $filename = pathinfo(basename($file), PATHINFO_FILENAME);;
        $journal = $this->readExcel($file);
        $generatedExcel = $this->generateExcel($journal, $filename);
        return 'Сформированный файл '.$generatedExcel.' содержит записей: '.count($journal);
    }

}