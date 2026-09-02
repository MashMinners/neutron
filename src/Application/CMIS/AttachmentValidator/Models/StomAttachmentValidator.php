<?php

namespace Application\CMIS\AttachmentValidator\Models;

use Application\CMIS\AttachmentValidator\Base\XmlParser;

class StomAttachmentValidator extends BaseAttachmentValidator
{
    public function __construct(private XmlParser $parser){

    }

    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        $unAttachedPacientIDs = $this->getUnAttachedPacientIDs($data['H']);
        $unAttachedPatients = $this->personify($data['L']['PERS'], $unAttachedPacientIDs);
        return $unAttachedPatients;
    }

}