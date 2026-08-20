<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Base;

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
        $header = ['Дата начала (ФОНД)', 'Дата окнчания (ФОНД)', 'Дата начала (РЕЕСТР)', 'Дата окончания (РЕЕСТР)',
            'Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'Полис', 'СНИЛС'];
        foreach ($header AS $singleHeader){
            $sheet->setCellValue($col . $row, $singleHeader);
            $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getColumnDimension($col)->setWidth(25);
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
    private function assembleDataSet(array $intersections) : array{
        $dataSet = [];
        $i = 0;
        foreach ($intersections AS $intersection){
            $dataSet[$i]['DATE_IN_TFOMS'] = $intersection['DATE_IN_TFOMS'];
            $dataSet[$i]['DATE_OUT_TFOMS'] = $intersection['DATE_OUT_TFOMS'];
            $dataSet[$i]['DATE_IN'] = $intersection['DATE_IN'];
            $dataSet[$i]['DATE_OUT'] = $intersection['DATE_OUT'];
            $dataSet[$i]['FAM'] = $intersection['FAM'];
            $dataSet[$i]['IM'] = $intersection['IM'];
            $dataSet[$i]['OT'] = $intersection['OT'];
            $dataSet[$i]['DR'] = $intersection['DR'];
            $dataSet[$i]['ENP'] = $intersection['ENP'];
            $dataSet[$i]['SNILS'] = $intersection['SNILS'];
            $i++;
        }
        return $dataSet;
    }
    public function generate(array $intersections, $fileName){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetWithHeader = $this->generateHeader($sheet);
        $dataSet = $this->assembleDataSet($intersections);
        $this->generateBody($sheetWithHeader, $dataSet);
        $writer = new Xlsx($spreadsheet);
        $file = 'storage/cmis/completed/'.$fileName.'.xlsx';
        $writer->save($file);
    }

}