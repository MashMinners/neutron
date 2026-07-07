<?php

namespace Application\TFOMS\MedicalBillingValidator\STAC\Models;

use Application\TFOMS\MedicalBillingValidator\Base\DataParser;
use Application\TFOMS\MedicalBillingValidator\Base\ResultFileMaker;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GuaranteedPaymentsMatcher
{
    public function __construct(private DataParser $parser, private ResultFileMaker $maker){

    }
    private $successFile = 'storage/tfoms/BillingValidator/Successful.xlsx';
    private $guaranteedFile = 'storage/tfoms/BillingValidator/KSDS.xlsx';
    public function getSuccessful(){
        $spreadsheet = IOFactory::load($this->successFile);
        $sheet = $spreadsheet->getActiveSheet();
        $startRow = 'A1';
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $rows = $sheet->rangeToArray(
            "$startRow:$highestColumn$highestRow", // Диапазон
            NULL,                          // Значение для пустых ячеек
            TRUE,                          // Вычислять формулы
            TRUE,                          // Форматировать значения (даты, проценты)
            TRUE                           // Использовать индексы строк/столбцов в массиве
        );
        return $rows;
    }

    private function getGuaranteed(){
        $spreadsheet = IOFactory::load($this->guaranteedFile);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->removeColumn('AR', 22);
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
        return $rows;
    }

    private function indicateWithUnique(array $successful, array $guaranteed){
        $successfulWithUniqueId = [];
        $guaranteedWithUniqueId = [];
        foreach ($successful AS $key => $value){
            $id = $value['C'].'-'.$value['N'].'-'.$value['O'].'-'.$value['P'];
            $successfulWithUniqueId[$id] = $value;
        }
        foreach ($guaranteed AS $key => $value){
            $id = $value['B'].'-'.$value['D'].'-'.$value['G'].'-'.$value['H'];
            $guaranteedWithUniqueId[$id] = $value;
        }
        return ['Successful' => $successfulWithUniqueId, 'Guaranteed' => $guaranteedWithUniqueId];
    }

    /**
     * Возвращает ID записей из первой таблицы,
     * которые есть во второй (гарантированные)
     */
    private function findMatches(array $indicatedWithUnique): array
    {
        return array_intersect_key($indicatedWithUnique['Successful'], $indicatedWithUnique['Guaranteed']);
    }

    /**
     * Возвращает ID записей из первой таблицы,
     * которых НЕТ во второй (не гарантированные)
     */
    public function findMissing(array $indicatedWithUnique): array
    {
        //Истории болезни залитые в ФОНД, но не повявившиеся в счете
        $missed = array_diff_key($indicatedWithUnique['Successful'], $indicatedWithUnique['Guaranteed']);
       return $missed;
    }

    private function findSurplus(array $indicatedWithUnique): array {
        //Истории болезник которые есть в счете, но в этом месяце не заливались в ФОНД, т.е. излишек
        $surplus = array_diff_key($indicatedWithUnique['Guaranteed'], $indicatedWithUnique['Successful']);
        return $surplus;
    }

    public function match(){
        $successful = $this->getSuccessful();
        $guaranteed = $this->getGuaranteed();
        $indicatedWithUnique = $this->indicateWithUnique($successful, $guaranteed);
        $matches = $this->findMatches($indicatedWithUnique);
        $missing = $this->findMissing($indicatedWithUnique);
        $surplus = $this->findSurplus($indicatedWithUnique);
        $this->maker->generateExcel($matches, 'Matches');
        $this->maker->generateExcel($missing, 'Missing');
        $this->maker->generateExcel($surplus, 'Surplus');
        return [
            'Всего в счете ТФОМС по КС и ДС' => count($guaranteed),
            'Не включены в счет, но поданы нами' => count($missing),
            'Включены в счет' => count($matches),
            'Излишек (не подано нами в этом месяце, но включено в счет на оплату)' => count($surplus)];
    }

}