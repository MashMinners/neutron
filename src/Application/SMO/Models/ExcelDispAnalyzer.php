<?php

namespace Application\SMO\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelDispAnalyzer
{
    private $gender = [
        'Мужской' => 1,
        'Женский' => 2
    ];

    private function getPURP($date){
        $purp = [
            18=>20,
            19=>20,
            20=>20,
            21=>20,
            22=>20,
            23=>20,
            24=>20,
            25=>20,
            26=>20,
            27=>20,
            28=>20,
            29=>20,
            30=>20,
            31=>20,
            32=>20,
            33=>20,
            34=>20,
            35=>20,
            36=>20,
            37=>20,
            38=>20,
            39=>20,
            40=>28,
            41=>28,
            42=>28,
            43=>28,
            44=>28,
            45=>28,
            46=>28,
            47=>28,
            48=>28,
            49=>28,
            50=>28,
            51=>28,
            52=>28,
            53=>28,
            54=>28,
            55=>28,
            56=>28,
            57=>28,
            58=>28,
            59=>28,
            60=>28,
            61=>28,
            62=>28,
            63=>28,
            64=>28,
            65=>29,
            66=>29,
            67=>29,
            68=>29,
            69=>29,
            70=>29,
            71=>29,
            72=>29,
            73=>29,
            74=>29,
            75=>29,
            76=>29,
            77=>29,
            78=>29,
            79=>29,
            80=>29,
            81=>29,
            82=>29,
            83=>29,
            84=>29,
            85=>29,
            86=>29,
            87=>29,
            88=>29,
            89=>29,
            90=>29,
            91=>29,
            92=>29,
            93=>29,
            94=>29,
            95=>29,
            96=>29,
            97=>29,
            98=>29,
            99=>29,
            100=>29
        ];
        $age = date_diff(date_create(), date_create($date))->format('%Y');
        return $purp[$age];
    }

    private function readBank($file) : array{
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        //unset($rows[0]);
        //unset($rows[$highestRow-1]);
        //Получить заголовки таблицы
        $excelTableHeader = array_shift($rows);
        //$isCorrect = $this->validateExcelSchema($excelTableHeader);
        //if ($isCorrect){
        //$excelData = $this->formatCasesForBank($rows);
        //}
        $needle = [];
        foreach ($rows AS $row){
            $uniqueId = $row[4].'-'.$row[7].'-'.$row[8];
            $fullName = explode(' ', $row[2]);
            $needle[$uniqueId]['uniqueId'] = $uniqueId;
            $needle[$uniqueId]['surname'] = $fullName[0];
            $needle[$uniqueId]['first_name'] = $fullName[1];
            $needle[$uniqueId]['second_name'] = $fullName[2] ?? '';
            $needle[$uniqueId]['gender'] = $this->gender[$row[45]];
            $needle[$uniqueId]['date_birth'] = $row[3];
            $needle[$uniqueId]['policy'] = $row[4];
            $needle[$uniqueId]['nusl'] = $row[5];
            $needle[$uniqueId]['date_start'] = $row[7];
            $needle[$uniqueId]['date_finish'] = $row[8];
            $needle[$uniqueId]['paid'] = $row[33];
            $needle[$uniqueId]['mek'] = $row[26];
        }
        return $needle;
    }

    private function readJournal($file) : array{
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        //unset($rows[0]);
        //unset($rows[$highestRow-1]);
        //Получить заголовки таблицы
        $excelTableHeader = array_shift($rows);
        //$isCorrect = $this->validateExcelSchema($excelTableHeader);
        //if ($isCorrect){
        //$excelData = $this->formatCasesForJournal($rows);
        //}
        $needle = [];
        foreach ($rows AS $row){
            $uniqueId = $row[9].'-'.$row[12].'-'.$row[13];
            $needle[$uniqueId ]['uniqueId'] = $uniqueId;
            $needle[$uniqueId ]['residence'] = $row[7];
            $needle[$uniqueId ]['snils'] = $row[8];
            $needle[$uniqueId ]['policy'] = $row[9];
            $needle[$uniqueId ]['assistance'] = $row[10];
            $needle[$uniqueId ]['diagnosis'] = $row[11];
            $needle[$uniqueId ]['price'] = $row[15];
            $needle[$uniqueId ]['result'] = $row[16];
        }
        return $needle;
    }

    private function compareExcel(array $bankExcel, array $journalExcel){
        $result = [];
        $intersect = array_intersect_key($bankExcel, $journalExcel);
        $count = 1;
        foreach ($intersect as $single){
            $result[$single['uniqueId']]['COUNT']= $count;
            $result[$single['uniqueId']]['NUSL'] = $bankExcel[$single['uniqueId']]['nusl'];
            $result[$single['uniqueId']]['SURNAME'] = $bankExcel[$single['uniqueId']]['surname'];
            $result[$single['uniqueId']]['FIRST_NAME'] = $bankExcel[$single['uniqueId']]['first_name'];
            $result[$single['uniqueId']]['SECOND_NAME'] = $bankExcel[$single['uniqueId']]['second_name'];
            $result[$single['uniqueId']]['GENDER'] = $bankExcel[$single['uniqueId']]['gender'];
            $result[$single['uniqueId']]['DATE_BIRTH'] = $bankExcel[$single['uniqueId']]['date_birth'];
            $result[$single['uniqueId']]['RESIDENCE'] = $journalExcel[$single['uniqueId']]['residence'];
            $result[$single['uniqueId']]['SNILS'] = $journalExcel[$single['uniqueId']]['snils'];
            $result[$single['uniqueId']]['POLICY'] = $bankExcel[$single['uniqueId']]['policy'];
            $result[$single['uniqueId']]['DIAGNOSIS'] = $journalExcel[$single['uniqueId']]['diagnosis'];
            $result[$single['uniqueId']]['ASSISTANCE'] = $journalExcel[$single['uniqueId']]['assistance'];
            $result[$single['uniqueId']]['DATE_START'] = $bankExcel[$single['uniqueId']]['date_start'];
            $result[$single['uniqueId']]['DATE_FINISH'] = $bankExcel[$single['uniqueId']]['date_finish'];
            $result[$single['uniqueId']]['PRICE'] = $journalExcel[$single['uniqueId']]['price'];
            $result[$single['uniqueId']]['RESULT'] = $journalExcel[$single['uniqueId']]['result'];
            $result[$single['uniqueId']]['PAID'] = $bankExcel[$single['uniqueId']]['paid'];
            $result[$single['uniqueId']]['MEK'] = $bankExcel[$single['uniqueId']]['mek'];
            $result[$single['uniqueId']]['PURP'] = $this->getPURP($bankExcel[$single['uniqueId']]['date_birth']);
            $count ++;
        }
        return $result;
    }

    private function generateExcel($dataExcel){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Запись из массива
        $data = [
            ['№ Позиции', 'Номер услуги', 'Фамилия', 'Имя', 'Отчество', 'Пол', 'Дата рождения', 'Место жительства',
                'СНИЛС', 'Полис', 'Диагноз МКБ-10', 'Вид помощи', 'Дата начала', 'Дата окончания', 'Стоимость',
                'Результат', 'Оплачено', 'Снято по МЭК', 'PURP'],
        ];
        $row = 1;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($col . $row, $cellData);
                $col++;
            }
            $row++;
        }
        $row = 2; // Начинаем с третьей строки
        foreach ($dataExcel as $rowData) {
            $col = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($col . $row, $cellData);
                $col++;
            }
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('storage/disp_1_'.date('d.m.Y_H_i_s').'.xlsx');
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