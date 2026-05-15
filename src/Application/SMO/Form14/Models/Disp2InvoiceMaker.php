<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Disp2InvoiceMaker extends BaseInvoicesMaker
{
    private $profile = 97;
    private $speciality = 76;
    protected $gender = [
        1 => 'Мужской',
        2 => 'Женский'
    ];

    private function readExcel(string $file){
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->removeColumn('A'); //Абсолютно пустая ненужная колонка. Осталась от программистов фонда
        $sheet->removeColumn('T'); //Абсолютно пустая ненужная колонка. Осталась от программистов фонда
        $sheet->removeColumn('U'); //Абсолютно пустая ненужная колонка. Осталась от программистов фонда
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
        $excelTableHeader['N'] = "Дата начала";
        $excelTableHeader['O'] = "Дата окончания";
        $excelTableHeader['P'] = "Код специальности";
        $excelTableHeader['Q'] = "Тариф";
        $rows = array_slice($rows, 2);
        array_splice($rows, -7);
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
            $needle[$uniqueId]['payment tariff'] = $row['P'];;
            $needle[$uniqueId]['cost'] = $row['Q'];
            $needle[$uniqueId]['result'] = $row['R'];
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