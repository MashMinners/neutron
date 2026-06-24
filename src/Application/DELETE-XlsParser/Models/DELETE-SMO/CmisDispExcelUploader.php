<?php

namespace Application\ExcelUploader\Models\SMO;

use Engine\Database\IConnector;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CmisDispExcelUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function validateExcelSchema(array $excelTableHeader) : bool {
        /*$workSchema = ['Номер записи в реестре случаев', 'Ф.И.О.', 'Дата рождения', 'Полис', 'Амб.талон/ Стат.карта', 'Дата начала лечения',
            'Дата окончания лечения', 'Диагноз основной', 'Результат обращения/ госпитализации', 'ФИО врача', 'Вид медицинской помощи',
            'Результат диспансеризации', 'PURP'
        ];*/
        $workSchema = ['Номер записи в реестре случаев', 'Полис', 'Дата начала лечения','Дата окончания лечения', 'Диагноз основной',
            'Результат обращения/ госпитализации', 'ФИО врача', 'Результат диспансеризации', 'PURP'
        ];
        return array_diff($excelTableHeader, $workSchema) === [] ? true : false;
    }

    private function formatCasesForCmis (array $cases) : array{
        foreach ($cases as $case){
            $case[2] = strtotime($case[2]);
            $case[5] = strtotime($case[5]);
            $case[6] = strtotime($case[6]);
            $formattedCases[$case[3]] = $case;
        }
        return $formattedCases;
    }

    private function formatCasesForTfoms (array $cases) : array{
        foreach ($cases as $case){
            $case[1] = strtotime($case[1]);
            $case[7] = strtotime($case[7]);
            $case[8] = strtotime($case[8]);
            $formattedCases[$case[4]] = $case;
        }
        return $formattedCases;
    }

    private function readCmisExcel($file) : array{
        $excelData = [];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        unset($rows[0]);
        //unset($rows[$highestRow-1]);
        //Получить заголовки таблицы
        $excelTableHeader = array_shift($rows);
        //$isCorrect = $this->validateExcelSchema($excelTableHeader);
        //if ($isCorrect){
        $excelData = $this->formatCasesForCmis($rows);
        //}
        return $excelData;
    }

    private function readTfomsExcel($file) : array{
        $excelData = [];
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
        $excelData = $this->formatCasesForTfoms($rows);
        //}
        return $excelData;
    }

    private function compareExcel(array $cmisExcel, array $tfomsExcel){
        $result = [];
        $intersect = array_intersect_key($cmisExcel, $tfomsExcel);
        foreach ($intersect as $single){
            $fullName = explode(' ', $tfomsExcel[$single[3]][0]);
            $result[$single[3]]['NUSL'] = $cmisExcel[$single[3]][0];
            $result[$single[3]]['SURNAME'] = $fullName[0];
            $result[$single[3]]['FIRST_NAME'] = $fullName[1];
            $result[$single[3]]['SECOND_NAME'] = $fullName[2];
            $result[$single[3]]['DATE_BIRTH'] = date('d.m.Y', $cmisExcel[$single[3]][2]);
            $result[$single[3]]['POLICY'] = $cmisExcel[$single[3]][3];
            $result[$single[3]]['TREATMENT_START'] = date('d.m.Y', $cmisExcel[$single[3]][5]);
            $result[$single[3]]['TREATMENT_END'] = date('d.m.Y', $cmisExcel[$single[3]][6]);
            $result[$single[3]]['DIAGNOSIS'] = $cmisExcel[$single[3]][7];
            $result[$single[3]]['APPEAL_RESULT'] = $cmisExcel[$single[3]][8];
            $result[$single[3]]['PAYMENT'] = $tfomsExcel[$single[3]][9]; //
            $result[$single[3]]['DOCTOR'] = $cmisExcel[$single[3]][9];
            $result[$single[3]]['DISP_RESULT'] = $cmisExcel[$single[3]][11];
            $result[$single[3]]['PURP'] = $cmisExcel[$single[3]][13];
            $result[$single[3]]['SPECFIC'] = 76;
            $result[$single[3]]['ADDRESS'] = $tfomsExcel[$single[3]][2];
            $result[$single[3]]['SNILS'] = $tfomsExcel[$single[3]][3];
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
            ['NUSL', 'SURNAME', 'FIRST_NAME', 'SECOND_NAME', 'DATE_BIRTH', 'POLICY', 'TREATMENT_START', 'TREATMENT_END', 'DIAGNOSIS',
                'APPEAL_RESULT', 'PAYMENT', 'DOCTOR', 'DISP_RESULT', 'PURP', 'SPECFIC', 'ADDRESS', 'SNILS'],
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
        $writer->save('storage/my_report.xlsx');
    }

    public function excelDataToMySQLData () {
        //Получаем данные из Excel
        $cmisDispExcelData = $this->readCmisExcel('storage/CMIS_DISP.xlsx');
        $tfomsDispExcelData = $this->readTfomsExcel('storage/TFOMS_DISP.xlsx');
        $result = $this->compareExcel($cmisDispExcelData, $tfomsDispExcelData);
        $this->generateExcel($result);
        return $result;
    }

    public function truncate() : void {
        $query = ("TRUNCATE TABLE `visits`");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

}