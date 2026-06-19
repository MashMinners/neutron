<?php

namespace Application\TFOMS\TargetGroupDistributor\Models;

use DateTime;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PatientTargetGroupDistributor
{
    private string $directory = "storage/tfoms/";

    private function getExcelData(){
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

    protected function getAgeInCurrentYear($birthDate){
        // Парсим дату рождения
        $birth = DateTime::createFromFormat('d.m.Y', $birthDate);
        if (!$birth) {
            throw new InvalidArgumentException('Неверный формат даты. Используйте дд.мм.гггг');
        }

        // Текущая дата
        $now = new DateTime();

        // Вычисляем возраст
        $age = $now->format('Y') - $birth->format('Y');

        // Проверяем, был ли уже день рождения в этом году
        $birthdayThisYear = new DateTime($now->format('Y') . '-' . $birth->format('m-d'));

        if ($now < $birthdayThisYear) {
            $age--; // Если день рождения ещё не наступил, уменьшаем возраст на 1
        }

        return $age;
    }

    protected function generateExcel(array $dataExcel, string $filename){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //Запись данных
        $row = 2; // Начинаем с третьей строки
        foreach ($dataExcel as $rowData) {
            $col = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($col . $row, $cellData);
                $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $file = 'storage/tfoms/distributed/'.$filename.'.xlsx';
        $writer->save($file);
        return $file;
    }


    public function distribute(){
        $data = $this->getExcelData();
        $target20 = [];
        $target28 = [];
        $target29 = [];
        foreach ($data AS $single){
            $yearsOld = $this->getAgeInCurrentYear($single['C']);
            if ($yearsOld > 65){
                $target29[] = $single;
            }elseif ($yearsOld < 65 AND $yearsOld > 40){
                $target28[] = $single;
            }else{
                $target20[] = $single;
            }
        }
        $this->generateExcel($target20, 'Цель 20 (18-39)');
        $this->generateExcel($target28, 'Цель 28 (40-64)');
        $this->generateExcel($target29, 'Цель 29 (65+)');
        return [
            '20 цель' => $target20,
            '28 цель' => $target28,
            '29 цель' => $target29
        ];
    }

}