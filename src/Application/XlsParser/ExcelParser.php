<?php

namespace Application\XlsParser;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelParser
{
    protected function readExcel($file){
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        //Получить все строки
        $rows = $sheet->toArray();
        //Убрать пустые, не нужные поля
        unset($rows[0]);
        return $rows;
    }

    /**
     * Получает индекс полей, определенных в схеме дочернего класса
     * Это необходимо, что бы брать в работу только те поля которые определены схемой
     * Потому что исходный файл может содержать большее количество полей
     * @param array $workSchema
     * @param array $excelTableHeader
     * @return array
     */
    protected function getExcelFieldsKeys(array $workSchema, array $excelTableHeader){
        $excelFieldsKeys = [];
        //Получаем по имени заголовка его ключ, для того чтобы по этому ключу искать данные
        foreach ($workSchema as $key => $value){
            $excelFieldsKeys[$value] = array_keys($excelTableHeader, $value)[0];
        }
        return $excelFieldsKeys;
    }

    /**
     * Форматируем дату в Linux time
     * @param string $case
     * @return int
     */
    protected function formatDates (string $case) : int{
        return strtotime($case);
    }

}