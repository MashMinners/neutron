<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class DPInvoiceValidator extends BaseDispValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){}

    private function parseD(array $file){
        foreach ($file['ZAP'] AS $key => $value){
            $idPac =$value['PACIENT'][0]['ID_PAC'];
            foreach ($value['Z_SL'][0]['SL'][0]['USL'] AS $key => $value){
                $data[$idPac][] = $value['CODE_USL'];
            }
        }
        return $data;
    }

    protected function parseF(string $file){

    }

    protected function parseL(array $file){
        foreach ($file['PERS'] AS $key => $value){
            $result[$value['ID_PAC']] = $value;
        }
        return $result;
    }

    private function compare(array $dData, array $lData){
        $data =[];
        foreach ($lData AS $key=> $value){
            $data[$key] = $this->getAgeInCurrentYear($value['DR']);
        }
        foreach ($dData AS $key => $value){
            $result[$key][$data[$key]] = $value;
        }
        return $result;
    }
    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        $dData = $this->parseD($data['D']);
        $lData = $this->parseL($data['L']);
        $compared = $this->compare($dData, $lData);
        return $data;
    }

}