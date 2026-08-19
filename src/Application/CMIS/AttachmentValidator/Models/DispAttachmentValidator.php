<?php

namespace Application\CMIS\AttachmentValidator\Models;

use Application\CMIS\AttachmentValidator\Base\XmlParser;

class DispAttachmentValidator extends BaseAttachmentValidator
{
    public function __construct(private XmlParser $parser){

    }
    /*
    private function getXmlFileName(array $files, string $pattern){
        $filtered = array_filter($files, function($item) use ($pattern){
            return preg_match($pattern, $item);
        });
        return ((string)reset($filtered));
    }
    private function getUnAttachedPacientIDs(array $D){
        $unAttachedRecord = [];
        foreach ($D['ZAP'] as $key => $value) {
            if (!array_key_exists('MO_PR', $value['PACIENT'][0])){
                $unAttachedRecord[$key] = $value;
            }
        }
        $unAttachedPacientIDs = [];
        foreach ($unAttachedRecord AS $single){
            $unAttachedPacientIDs[] = $single['PACIENT'][0]['ID_PAC'];
        }
        return $unAttachedPacientIDs;
    }
    private function getUnAttachedPatients(array $PERS, array $pacientIDs){
        $unAttachedPatients = [];
        foreach ($PERS AS $key => $value){
            if (in_array($value['ID_PAC'], $pacientIDs)){
                $unAttachedPatients[$key] = $value;
            }
        }
        return $unAttachedPatients;
    }*/
    public function validate(array $files){
        $xmlFiles['D'] = $this->getXmlFileName($files, '/^D/');
        $xmlFiles['F'] = $this->getXmlFileName($files, '/^F/');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/^L/');
        $data = $this->parser->parseXML($xmlFiles);
        $unAttachedPacientIDs = $this->getUnAttachedPacientIDs($data['D']);
        $unAttachedPatients = $this->getUnAttachedPatients($data['L']['PERS'], $unAttachedPacientIDs);
        return $unAttachedPatients;
    }

}