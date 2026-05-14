<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class AppDiagInvoiceMaker extends BaseInvoicesMaker
{
    private function readExcel(string $file){
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->removeColumn('T');
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
            //$uniqueId = $row['J'].'-'.$dates[0].'-'.$dates[1].'-'.$row['S'];
            $needle[$position]['position'] = $position;
            $needle[$position]['full_name'] = $row['B'];
            //$needle[$uniqueId]['gender'] = $this->gender[$row['C']];
            $needle[$position]['gender'] = $row['C'];
            $needle[$position]['date_birth'] = $row['D'];
            $needle[$position]['place_of_birth'] = 'Приморский край';
            $needle[$position]['identity_document'] = $row['F'];
            $needle[$position]['snils'] = $row['I'];
            $needle[$position]['policy'] = $row['J'];
            $needle[$position]['medical_care'] = $row['K'];
            $needle[$position]['diagnosis'] = $row['L'];
            $needle[$position]['date_start'] = $dates[0];
            $needle[$position]['date_finish'] = $dates[1];
            $needle[$position]['volumes'] = 1;
            $needle[$position]['profile'] = $row['O'];
            $needle[$position]['speciality'] = $row['P'];
            $needle[$position]['payment tariff'] = $row['Q'];
            $needle[$position]['cost'] = $row['R'];
            $needle[$position]['result'] = $row['S'];
            $position++;
        }
        return $needle;
    }

    public function makeInvoice(string $file){
        $filename = pathinfo(basename($file), PATHINFO_FILENAME);;
        $journal = $this->readExcel($file);
        $this->generateExcel($journal, $filename);
        return 'Сформированный файл содержит записей: '.count($journal);
    }

}