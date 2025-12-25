<?php

namespace Application\SMO\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelSTOMAnalyzer
{
    private $gender = [
        'Мужской' => 1,
        'Женский' => 2
    ];

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
            $profil = preg_replace('/\D/', '', $row[14]);
            $uniqueId = $row[4].'-'.$profil.'-'.$row[7].'-'.$row[8];
            $fullName = explode(' ', $row[2]);
            $needle[$uniqueId]['uniqueId'] = $uniqueId;
            $needle[$uniqueId]['surname'] = $fullName[0];
            $needle[$uniqueId]['first_name'] = $fullName[1];
            $needle[$uniqueId]['second_name'] = $fullName[2];
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
            $dates= explode(' - ', $row[12]);
            $dateStart = $dates[0];
            $dateFinish = $dates[1];
            $uniqueId = $row[9].'-'.$row[14].'-'.$dateStart.'-'.$dateFinish;
            $needle[$uniqueId ]['uniqueId'] = $uniqueId;
            $needle[$uniqueId ]['residence'] = $row[6];
            $needle[$uniqueId ]['snils'] = $row[8];
            $needle[$uniqueId ]['policy'] = $row[9];
            $needle[$uniqueId ]['diagnosis'] = $row[11];
            $needle[$uniqueId ]['profil'] = $row[14];
            $needle[$uniqueId ]['spec'] = $row[15];
            $needle[$uniqueId ]['price'] = $row[17];
            $needle[$uniqueId ]['result'] = $row[18];
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
            $result[$single['uniqueId']]['DATE_START'] = $bankExcel[$single['uniqueId']]['date_start'];
            $result[$single['uniqueId']]['DATE_FINISH'] = $bankExcel[$single['uniqueId']]['date_finish'];
            $result[$single['uniqueId']]['PROFIL'] = $journalExcel[$single['uniqueId']]['profil'];
            $result[$single['uniqueId']]['SPEC'] = $journalExcel[$single['uniqueId']]['spec'];
            $result[$single['uniqueId']]['PRICE'] = $journalExcel[$single['uniqueId']]['price'];
            $result[$single['uniqueId']]['RESULT'] = $journalExcel[$single['uniqueId']]['result'];
            $result[$single['uniqueId']]['PAID'] = $bankExcel[$single['uniqueId']]['paid'];
            $result[$single['uniqueId']]['MEK'] = $bankExcel[$single['uniqueId']]['mek'];
            $count ++;
        }
        return $result;
    }

    private function generateExcel($dataExcel){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //$sheet->setCellValue('A1', 'Привет, мир!');
        //$sheet->setCellValue('B2', 'Данные в ячейке B2');
        // Запись из массива
        $data = [
            ['№ Позиции', 'Номер услуги', 'Фамилия', 'Имя', 'Отчество', 'Пол', 'Дата рождения', 'Место жительства',
                'СНИЛС', 'Полис', 'Диагноз МКБ-10', 'Дата начала', 'Дата окончания', 'Профиль оказанной мед. помощи',
                'Специальность медицинского работника', 'Стоимость', 'Результат', 'Оплачено', 'Снято по МЭК'],
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
        $writer->save('storage/stom_'.date('d.m.Y_H_i_s').'.xlsx');
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