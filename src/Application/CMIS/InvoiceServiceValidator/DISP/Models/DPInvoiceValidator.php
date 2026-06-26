<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class DPInvoiceValidator extends BaseDispValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){

    }
    private function getUslGroupedByAge(array $usl, array $pers){
        $data =[];
        foreach ($pers AS $key=> $value){
            $data[$key] = $this->getAgeInCurrentYear($value['DR']);
        }
        foreach ($usl AS $key => $value){
            //$result[$key][$data[$key]] = $value;
            $result[$key]['USL'][$data[$key]] = $value;
            $result[$key]['PERS'] = $pers[$key];
        }
        return $result;
    }
    private function validateWithSample(array $uslArray){
        //Получаю шаблоны для первого этапа
        $sample = $this->getSample('DP');
        $validated = [];
        foreach ($uslArray AS $id => $usl){
            $age = key($usl['USL']);
            $diff = array_diff($usl['USL'][$age], $sample[$age][$usl['PERS']['W']]);
            $validated[$id]['PERS'] = $usl['PERS'];
            $validated[$id]['PERS']['AGE'] = $age;
            $validated[$id]['USL'] = $diff;
        }
        return $validated;
    }
    public function validate(array $files){
        //Получаю распарсенные XML
        $data = $this->parser->parseXML($files);
        $usl = $this->getUslGroupedByIdPac($data['D']);
        $pers = $this->getPersGroupedByIdPac($data['L']);
        //Группировка услуг в соответсвии с возрастом
        $uslGroupedByAge = $this->getUslGroupedByAge($usl, $pers);
        //Валидации сгруппированных по возрасту услуг с шаблонами соответствующих фонду
        $validationResult = $this->validateWithSample($uslGroupedByAge);
        $persons = $this->getPersons($validationResult);
        (new ExcelGenerator())->generate($persons);
        return ['Количество записей всего' => count($data['L']['PERS']), 'Количество записей с ошибками' => count($persons), 'Записи' => $persons];
    }

}