<?php

namespace Application\TFOMS\MedicalBillingValidator\Base;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResultFileMaker
{
    public function generateExcel($dataExcel, $filename){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //Запись данных
        $row = 1; // Начинаем с третьей строки
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
        $file = 'storage/tfoms/BillingValidator/completed/'.$filename.'.xlsx';
        $writer->save($file);
        return $file;
    }

}