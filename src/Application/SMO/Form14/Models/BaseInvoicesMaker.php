<?php

namespace Application\SMO\Form14\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BaseInvoicesMaker
{
    protected $gender = [
        'Мужской' => 1,
        'Женский' => 2
    ];
    protected $mounths = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12=> 'Декабрь'
    ];

    protected function formatAcceptableFilesNames(string $file){
        $pattern = '/([^\/]+)-(\d+)$/u';
        if (preg_match($pattern, $file, $matches)) {
            return [$matches[1], $matches[2]];
        }
    }

    protected function getPeriod(){
        $month = $this->mounths[date('n')-1];
        $year = date('Y');
        return "за период $month $year года";
    }

    protected function generateExcelHeader($sheet, $invoiceNumber){
        //1
        $sheet->mergeCells("B1:Q1");
        $sheet->setCellValue("B1", 'РЕЕСТР СЧЕТОВ № '.$invoiceNumber);
        //2
        $sheet->mergeCells("B2:Q2");
        $sheet->setCellValue("B2", 'КГБУЗ "Чугуевская центральная районная больница", ОГРН 1022500509415');
        //3
        $sheet->mergeCells("B3:Q3");
        $sheet->setCellValue("B3", '(наименование медицинской организации, ОГРН в соответствии с ЕГРЮЛ)');
        //4
        $sheet->mergeCells("B4:Q4");
        $sheet->setCellValue("B4", $this->getPeriod());
        //5
        $sheet->mergeCells("B5:Q5");
        $sheet->setCellValue("B5", 'на оплату медицинской помощи, оказанной застрахованным лицам, в ООО СМО  "Восточно-страховой альянс"');
        for ($row = 2; $row<6; $row++){
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B$row")->getFont()->setName('Times New Roman')->setSize(12);
        }
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1')->getFont()->setName('Times New Roman')->setSize(14);
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $data = [
            '№ Позиции', 'Фамилия, имя, отчество', 'Пол', 'Дата рождения', 'Место рождения',
            'Данные документа, удостоверяющего личность', 'Снилс', 'Полис', 'Вид оказанной медицинской помощи (код)',
            'Диагноз МКБ-10', 'Дата начала лечения', 'Дата окончания лечения', 'Объемы оказанной медицинской помощи',
            'Профиль оказанной медицинской помощи', 'Специальность медицинского работника', 'Тариф на оплату',
            'Стоимость оказанной помощи', 'Результат обращения'
        ];
        $col = 'A';
        foreach ($data as $cellData) {
            $sheet->setCellValue($col.'6', $cellData);
            $sheet->getStyle($col)->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('B')->setWidth(50);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getStyle($col.'6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($col.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col.'6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);;
            $sheet->getStyle($col.'6')->getFont()->setBold(true);
            $col++;
        }
        return $sheet;
    }
    protected function generateExcel(array $dataExcel, string $filename){
        $spreadsheet = new Spreadsheet();
        $formattedName = $this->formatAcceptableFilesNames($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet = $this->generateExcelHeader($sheet, $formattedName[1]);
        //Запись данных
        $row = 7; // Начинаем с третьей строки
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
        $file = 'storage/smo/[Счет ТФОМС №'.$formattedName[1].'] '.$formattedName[0].'.xlsx';
        $writer->save($file);
        return $file;
    }


}