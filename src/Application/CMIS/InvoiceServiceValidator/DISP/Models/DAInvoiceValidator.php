<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class DAInvoiceValidator extends BaseDispValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){

    }

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
    private function getPersons(array $validationResult){
        /*$validatedPersons = [];
        foreach ($validationResult AS $id => $usl){
            if (!empty($usl)){
                $validatedPersons[$id]['PERS'] = $pers[$id];
                $validatedPersons[$id]['USL'] = $usl;
            }
        }
        return $validatedPersons;*/
        $validatedPersons = [];
        foreach ($validationResult AS $id => $result){
            if (!empty($result['USL'])){
                $validatedPersons[$id] = $result;
            }
        }
        return $validatedPersons;
    }

    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        $usl = $this->getUslGroupedByIdPac($data['D']);
        $pers = $this->getPersGroupedByIdPac($data['L']);
        $uslGroupedWithPers = $this->getUslGroupedWithPers($usl, $pers);
        $validationResult = $this->validateWithSample($uslGroupedWithPers);
        $persons = $this->getPersons($validationResult);
        return ['Количество записей всего' => count($data['L']['PERS']), 'Количество записей с ошибками' => count($persons), 'Записи' => $persons];
    }


}