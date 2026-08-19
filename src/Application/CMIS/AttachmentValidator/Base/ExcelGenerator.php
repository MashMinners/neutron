<?php

namespace Application\CMIS\AttachmentValidator\Base;

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
        $header = ['ID_PAC', 'Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС'];
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

    private function generateBody($sheet, $patients){
        $row = 2;
        foreach ($patients AS $patient){
            $col = 'A';
            foreach ($patient AS $key => $value){
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($col.$row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD3D3D3');
                $col++;
            }
            $row++;
        }
        return $sheet;
    }
    public function generate(array $patients, $fileName){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetWithHeader = $this->generateHeader($sheet);
        $this->generateBody($sheetWithHeader, $patients);
        $writer = new Xlsx($spreadsheet);
        $file = 'storage/cmis/completed/'.$fileName.'.xlsx';
        $writer->save($file);
    }

}