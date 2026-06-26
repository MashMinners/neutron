<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGenerator
{
    public function generate(array $persons){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 'A';
        $header = ['ID_PAC', 'Фамилия', 'Имя', 'Отчество', 'Пол', 'Дата рождения', 'СНИЛС', 'ОКАТО 1', 'ОКАТО 2', 'Возвраст'];
        foreach ($header AS $singleHeader){
            $sheet->setCellValue($col . $row, $singleHeader);
            $col++;
        }
        $row = 2;
        foreach ($persons AS $person){
            $pers = $person['PERS'];
            $usl = $person['USL'];
            $col = 'A';
            foreach ($pers AS $key => $value){
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }
            $row++;
            foreach ($usl AS $single){
                $sheet->mergeCells("A$row:J$row");
                $sheet->setCellValue("A$row", 'Услуга '.$single);
                $row++;
            }
        }
        $file = 'File';
        $writer = new Xlsx($spreadsheet);
        $file = 'storage/cmis/completed/'.$file.'.xlsx';
        $writer->save($file);
    }

}