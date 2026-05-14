<?php

namespace Application\SMO\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelKSAnalyzer
{
    private function getExcelFieldsKeys(){

    }

    private function readBank($file) : array{
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $rows = $sheet->toArray();
        $excelTableHeader = array_shift($rows);
        //unset($rows[0]);
    }

    private function readJournal($file) : array{
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        //Получить все строки
        $rows = $sheet->toArray();
        $excelTableHeader = array_shift($rows);


    }

    public function analyze (string $bank, string $journal) {
        //Получаем данные из Excel
        $excelBankData = $this->readBank($bank);
        $excelJournalData = $this->readJournal($journal);
        $compared = $this->compareExcel($excelBankData, $excelJournalData);
        $this->generateExcel($compared);
        return 'Сформированный файл содержит записей: '.count($compared);
    }

}