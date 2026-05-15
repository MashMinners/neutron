<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BaseInvoicesMaker
{
    protected $gender = [
        'Мужской' => 1,
        'Женский' => 2
    ];

    protected function formatAcceptableFilesNames(string $file){
        $pattern = '/([^\/]+)-(\d+)$/u';

        if (preg_match($pattern, $file, $matches)) {
            return [$matches[1], $matches[2]];
        }
    }
    protected function generateExcel(array $dataExcel, string $filename){
        $spreadsheet = new Spreadsheet();
        $formattedName = $this->formatAcceptableFilesNames($filename);
        $sheet = $spreadsheet->getActiveSheet();
        // Запись из массива
        $data = [
            ['№ Позиции', 'Фамилия, имя, отчество', 'Пол', 'Дата рождения', 'Место рождения',
                'Данные документа, удостоверяющего личность', 'Снилс', 'Полис', 'Вид оказанной медицинской помощи (код)',
                'Диагноз МКБ-10', 'Дата начала лечения', 'Дата окончания лечения', 'Объемы оказанной медицинской помощи',
                'Профиль оказанной медицинской помощи', 'Специальность медицинского работника', 'Тариф на оплату',
                'Стоимость оказанной помощи', 'Результат обращения']
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
        //$writer->save('storage/smo/['.$formattedName[1].'] '.$formattedName[0].'_'.date('d.m.Y_H_i_s').'.xlsx');
        $file = 'storage/smo/['.$formattedName[1].'] '.$formattedName[0].'.xlsx';
        $writer->save($file);
        return $file;
    }


}