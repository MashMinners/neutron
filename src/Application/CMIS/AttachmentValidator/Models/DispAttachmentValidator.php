<?php

namespace Application\CMIS\AttachmentValidator\Models;

use Application\CMIS\AttachmentValidator\Base\XmlParser;

class DispAttachmentValidator extends BaseAttachmentValidator
{
    public function __construct(private XmlParser $parser){

    }
    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        $unAttachedPacientIDs = $this->getUnAttachedPacientIDs($data['D']);
        $unAttachedPatients = $this->personify($data['L']['PERS'], $unAttachedPacientIDs);
        return $unAttachedPatients;
    }

}