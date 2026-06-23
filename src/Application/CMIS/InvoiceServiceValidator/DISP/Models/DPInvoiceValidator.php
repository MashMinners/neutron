<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class DPInvoiceValidator extends BaseDispValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){}

    private function getUslGroupedByIdPac(array $file){
        foreach ($file['ZAP'] AS $key => $value){
            $idPac =$value['PACIENT'][0]['ID_PAC'];
            foreach ($value['Z_SL'][0]['SL'][0]['USL'] AS $key => $value){
                $data[$idPac][] = $value['CODE_USL'];
            }
        }
        return $data;
    }
    private function getPersGroupedByIdPac(array $file){
        foreach ($file['PERS'] AS $key => $value){
            $result[$value['ID_PAC']] = $value;
        }
        return $result;
    }
    private function getUslGroupedByAge(array $usl, array $pers){
        $data =[];
        foreach ($pers AS $key=> $value){
            $data[$key] = $this->getAgeInCurrentYear($value['DR']);
        }
        foreach ($usl AS $key => $value){
            $result[$key][$data[$key]] = $value;
        }
        return $result;
    }
    private function validateWithSample(array $uslArray){
        //Получаю шаблоны для первого этапа
        $sample = $this->getSample('DP');
        $validated = [];
        foreach ($uslArray AS $id => $usl){
            $age = key($usl);
            $sampleUsl = $sample[$age];
            $uslUsl = $usl[$age];
            if (array_values($sample[$age]) === array_values($usl[$age])){
                $validated['good'][] = $id;
            }
            else $validated['bad'][] = $id;
        }
        return $validated;
    }
    private function getPersons(array $pers, array $validationResult){
        $validatedPersons = [];
        $notValidatedPersons = [];
        foreach ($validationResult['good'] AS $id){
            $validatedPersons[$id] = $pers[$id];
        }
        foreach ($validationResult['bad'] AS $id){
            $notValidatedPersons[$id] = $pers[$id];
        }
        return ['validated' => $validatedPersons, 'notValidated' => $notValidatedPersons];
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
        $persons = $this->getPersons($pers, $validationResult);
        return $persons;
    }

}