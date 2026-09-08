<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

use Application\CMIS\InvoiceServiceValidator\DISP\Base\DataParser;
use Application\CMIS\InvoiceServiceValidator\DISP\Base\ExcelGenerator;

class DAInvoiceValidator extends BaseDispValidator
{
    public function __construct(private DataParser $parser){

    }
    private function getUslGroupedWithPers(array $usl, array $pers){
        foreach ($usl AS $key => $value){
            $result[$key]['USL'] = $value;
            $result[$key]['PERS'] = $pers[$key];
        }
        return $result;
    }
    private function validateWithSample(array $uslArray){
        //Получаю шаблоны для первого этапа
        $sample = $this->getSample('DA');
        $validated = [];
        foreach ($uslArray AS $id => $usl){
            $age = key($usl['USL']);
            $diff = array_diff($usl['USL'], $sample);
            $validated[$id]['PERS'] = $usl['PERS'];
            $validated[$id]['USL'] = $diff;
        }
        return $validated;
    }
    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        $usl = $this->getUslGroupedByIdPac($data['D']);
        $pers = $this->getPersGroupedByIdPac($data['L']);
        $uslGroupedWithPers = $this->getUslGroupedWithPers($usl, $pers);
        $validationResult = $this->validateWithSample($uslGroupedWithPers);
        $persons = $this->getPersons($validationResult);
        return $persons;
    }

}