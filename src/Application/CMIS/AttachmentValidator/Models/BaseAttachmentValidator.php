<?php

namespace Application\CMIS\AttachmentValidator\Models;

class BaseAttachmentValidator
{
    protected function getUnAttachedPacientIDs(array $D){
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

    protected function personify(array $PERS, array $pacientIDs) : array{
        $unAttachedPatients = [];
        foreach ($PERS AS $key => $value){
            if (in_array($value['ID_PAC'], $pacientIDs)){
                $unAttachedPatients[$key]['ID_PAC'] = $value['ID_PAC'];
                $unAttachedPatients[$key]['FAM'] = $value['FAM'];
                $unAttachedPatients[$key]['IM'] = $value['IM'];
                $unAttachedPatients[$key]['OT'] = $value['OT'];
                $unAttachedPatients[$key]['DR'] = $value['DR'];
                $unAttachedPatients[$key]['SNILS'] = $value['SNILS'];
            }
        }
        return $unAttachedPatients;
    }

}