<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGenerator
{
    private function generateHeader($sheet){
        $row = 1;
        $col = 'A';
        $header = ['ID_PAC', 'Фамилия', 'Имя', 'Отчество', 'Пол', 'Дата рождения', 'СНИЛС', 'ОКАТО 1', 'ОКАТО 2', 'Возвраст'];
        foreach ($header AS $singleHeader){
            $sheet->setCellValue($col . $row, $singleHeader);
            $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getColumnDimension($col)->setWidth(20);
            $sheet->getStyle($col.$row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB(Color::COLOR_BLACK);
            $sheet->getStyle($col.$row)
                ->getFont()
                ->getColor()
                ->setARGB('FFFFFFFF');
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }
        return $sheet;
    }

    private function generateBody($sheet, $persons){
        $row = 2;
        foreach ($persons AS $person){
            $pers = $person['PERS'];
            $usl = $person['USL'];
            $col = 'A';
            foreach ($pers AS $key => $value){
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($col.$row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD3D3D3');
                $col++;
            }
            $row++;
            foreach ($usl AS $single){
                $sheet->mergeCells("A$row:J$row");
                $sheet->setCellValue("A$row", 'Услуга '.$single);
                $sheet->getStyle("A$row")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF6B6B');
                $row++;
            }
        }
        $sheet->mergeCells("A$row:J$row");
        $sheet->setCellValue("A$row", 'Всего случаев с ошибками '.count($persons));
        return $sheet;
    }
    public function generate(array $persons){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetWithHeader = $this->generateHeader($sheet);
        $this->generateBody($sheetWithHeader, $persons);
        $file = 'Диспансеризация 1 этап';
        $writer = new Xlsx($spreadsheet);
        $file = 'storage/cmis/completed/'.$file.'.xlsx';
        $writer->save($file);
    }

}