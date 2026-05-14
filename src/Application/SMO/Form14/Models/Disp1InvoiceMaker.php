<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Disp1InvoiceMaker extends BaseInvoicesMaker
{
    private $profile = 97;
    private $speciality = 76;
    protected $gender = [
        'м' => 'Мужской',
        'ж' => 'Женский'
    ];

    private function readExcel(string $file){
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->removeColumn('R'); //Абсолютно пустая ненужная колонка. Осталась от программистов фонда
        $startRow = "A14";
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $rows = $sheet->rangeToArray(
            "$startRow:$highestColumn$highestRow", // Диапазон
            NULL,                          // Значение для пустых ячеек
            TRUE,                          // Вычислять формулы
            TRUE,                          // Форматировать значения (даты, проценты)
            TRUE                           // Использовать индексы строк/столбцов в массиве
        );
        $excelTableHeader = array_shift($rows);
        $excelTableHeader['M'] = "Дата начала";
        $excelTableHeader['N'] = "Дата окончания";
        $excelTableHeader['O'] = "Код специальности";
        $rows = array_slice($rows, 2);
        array_splice($rows, -6);
        $needle = [];
        $position = 1;
        foreach ($rows AS $row){

            $uniqueId = $row['J'].'-'.$row['M'].'-'.$row['N'];
            $needle[$uniqueId]['position'] = $position;
            $needle[$uniqueId]['full_name'] = $row['B'];
            $needle[$uniqueId]['gender'] = $this->gender[$row['C']];
            $needle[$uniqueId]['date_birth'] = $row['D'];
            $needle[$uniqueId]['place_of_birth'] = 'Приморский край';
            $needle[$uniqueId]['identity_document'] = $row['F'];
            $needle[$uniqueId]['snils'] = $row['I'];
            $needle[$uniqueId]['policy'] = $row['J'];
            $needle[$uniqueId]['medical_care'] = $row['K'];
            $needle[$uniqueId]['diagnosis'] = $row['L'];
            $needle[$uniqueId]['date_start'] = $row['M'];
            $needle[$uniqueId]['date_finish'] = $row['N'];
            $needle[$uniqueId]['volumes'] = 1;
            $needle[$uniqueId]['profile'] = $this->profile; //Профиль терапевта
            $needle[$uniqueId]['speciality'] = $this->speciality; //Специальность терапевта
            $needle[$uniqueId]['payment tariff'] = '';
            $needle[$uniqueId]['cost'] = $row['P'];
            $needle[$uniqueId]['result'] = $row['Q'];
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