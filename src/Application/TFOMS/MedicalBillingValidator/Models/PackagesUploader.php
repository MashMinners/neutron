<?php

namespace Application\TFOMS\MedicalBillingValidator\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PackagesUploader
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

    private function generateExcel($dataExcel, $filename){
        $spreadsheet = new Spreadsheet();
        //$formattedName = $this->formatAcceptableFilesNames($filename);
        $sheet = $spreadsheet->getActiveSheet();
        //$sheet = $this->generateExcelHeader($sheet, $formattedName[1]);
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
        $file = 'storage/tfoms/completed/'.$filename.'.xlsx';
        $writer->save($file);
        return $file;
    }

    private function getDefected($data){
        $defected =[];
        foreach ($data AS $key => $value){
            if ($value['H'] === '1'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $defected[$uniqueId] = $value;
            }
        }
        return $defected;
    }

    private function getSuccessful($data){
        $successful =[];
        foreach ($data AS $key => $value){
            if ($value['H'] === '0' && $value['I'] === '0'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $successful[$uniqueId] = $value;
            }
        }
        return $successful;
    }

    private function getCanceled($data){
        $canceled =[];
        foreach ($data AS $key => $value){
            if ($value['I'] === '1'){
                $uniqueId = $value['A'].'-'.$value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['M'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
                $canceled[$uniqueId] = $value;
            }
        }
        return $canceled;
    }

    private function getNonReturn(array $standard, array $successful, array $defective){
        $nonReturn = [];
        foreach ($standard as $key => $name) {
            $hasZero = isset($successful[$key]) && $successful[$key] > 0;
            $hasOne = isset($defective[$key]) && $defective[$key] > 0;

            // Невозврат: есть хотя бы одна 1, но нет ни одного 0
            if ($hasOne && !$hasZero) {
                $nonReturn[$key] = $name;
            }
        }
        return $nonReturn;
    }

    private function indicateWithUnique(array $result){
        $indicated = [];
        foreach ($result as $item) {
            /**
             * Был пациент Коновалов, совпадал по всем полям, кроме поля А (оказывается в один и тот же день, был у двух врачей)
             * потому и поле "Код случая отличался
             * Поля О и Р нужны так же, так как в стационаре коды случаев идут как 1,2,3 и тд, соответственно и определить уникальность можно по дате
             * нахождения в стационаре
             */
            $uniqueId = $item['A'].'-'.$item['B'].'-'.$item['C'].'-'.$item['D'].'-'.$item['H'].'-'.$item['M'].'-'.$item['N'].'-'.$item['O'].'-'.$item['P'];
            $indicated[$uniqueId] = $item;
        }
        return $indicated;
    }

    private function unify(array $unique){
        $standard = [];
        foreach ($unique AS $key => $value){
            $uniqueId = $value['B'].'-'.$value['C'].'-'.$value['D'].'-'.$value['N'];
            $standard[$uniqueId] = $value;
        }
        return $standard;
    }

    public function upload(){
        //Получить данные из всех файлов в папке
        $excelData = $this->getExcelData();
        //Получить только уникальные записи исключив двойники по проблемным загрузкам
        $unique = $this->indicateWithUnique($excelData);
        //Получить все записи с ошибками при загрузках
        $defected = $this->getDefected($unique);
        //Получить все записи с успешными загрузками
        $successful = $this->getSuccessful($unique);
        //Получить данные по удаленным из оплаты записям
        $canceled = $this->getCanceled($unique);
        //На выходе должно получаться столько же записей,сколько и вошло, но с унифицированным идентификатором
        $unifiedUnique = $this->unify($unique);
        //На выходе должно получаться столько же записей,сколько и вошло, но с унифицированным идентификатором
        $unifiedSuccessful = $this->unify($successful);
        /**
         * На выходе должно получаться МЕНЬШЕ записей,чем вошло и с унифицированным идентификатором.
         * Записей будет меньше в том случае, если были случаи переподаны в ФОНД и они снова повторялись с отказами
         */
        $unifyDefected = $this->unify($defected);
        /**
         * Здесь уже пошагово кажду запись сравнивают с уникальными строками и если такая запись есть в дефектных,
         * но нет в успешных, то считается что запись не будет подана на оплату ибо не прошла проверки ТФОМС
         */
        $nonReturn = $this->getNonReturn($unifiedUnique, $unifiedSuccessful, $unifyDefected);
        //Генерация файлов Excel
        $this->generateExcel($defected, 'Defected');
        $this->generateExcel($successful, 'Successful');
        $this->generateExcel($nonReturn, 'Non Return');
        $this->generateExcel($canceled, 'Canceled');
        return [
            'Всего записей' => count($excelData),
            'Уникальные (для расчета дефект/не дефект)' => count($unique),
            'Есть дефекты' =>  count($defected),
            'Залиты без дефектов' =>  count($successful),
            'Удалены из оплаты' =>  count($canceled),
            'Невозврат' =>  count($nonReturn)
        ];
    }

}