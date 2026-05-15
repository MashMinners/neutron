<?php

namespace Application\SMO\Form14\Models;

class Form14Aggregator
{
    private string $pathFolder = "storage/smo/";
    private array$acceptableFiles = [
        '1й этап дисп',
        '2й этап дисп',
        'Проф осмотры',
        'Проф осмотры несоверш',
        '1й этап углуб дисп',
        'АПП_ДН',
        'АПП_ДН_ОНКО',
        'Дисп.Репродуктивное здоровье 1 этап',
        'Апп онкология',
        'ТП ОМС-КСТАЦ',
        'ТП ОМС-ДСТАЦ',
        'АПП_ЗНО ФАП',
        'АПП ФАП',
        'АПП(диагност услуги)',
        'АПП',
        'СМП',
    ];

    private array $makers = [
        '1й этап дисп' => Disp1InvoiceMaker::class,
        '2й этап дисп' => Disp2InvoiceMaker::class,
        'Проф осмотры' => ExamInvoiceMaker::class,
        'Проф осмотры несоверш' => ExamChildrenInvoiceMaker::class,
        '1й этап углуб дисп' =>DispInDepthInvoiceMaker::class,
        'АПП_ДН' => AppDNInvoiceMaker::class,
        'АПП_ДН_ОНКО' => AppDNOncoInvoiceMaker::class,
        'Дисп.Репродуктивное здоровье 1 этап' => DprInvoiceMaker::class,
        'Апп онкология' => AppOncoInvoiceMaker::class,
        'ТП ОМС-КСТАЦ' => KSInvoiceMaker::class,
        'ТП ОМС-ДСТАЦ' => DSInvoiceMaker::class,
        'АПП_ЗНО ФАП' => AppOncoFAPInvoiceMaker::class,
        'АПП ФАП' => AppFAPInvoiceMaker::class,
        'АПП(диагност услуги)' => AppDiagInvoiceMaker::class,
        'АПП' => AppInvoiceMaker::class,
        'СМП' => SMPInvoiceMaker::class
    ];

    private function formatAcceptableFilesNames(string $file){
        $pattern = '/([^\/]+)-(\d+)\.xlsx$/u';;

        if (preg_match($pattern, $file, $matches)) {
            return [$matches[1], $matches[2]];
        }
    }
    public function aggregate(){
        $files = glob($this->pathFolder . '*.xlsx');
        //Сравниваем название файлов с допустимыми и по тем чьи названия совпали делаем переработку файлов по очереди
        $filesNames = [];
        foreach ($files AS $file){
            $formatted = $this->formatAcceptableFilesNames($file);
            $filesNames[$file] = $formatted[0];
        }
        $accepted = array_intersect($filesNames, $this->acceptableFiles);
        $message = [];
        foreach ($accepted AS $file => $maker){
            $message[] = (new $this->makers[$maker])->makeInvoice($file);
        }
        return $message;
    }

}