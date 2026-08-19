<?php

namespace Application\CMIS\AttachmentValidator\Models;

use Application\CMIS\AttachmentValidator\Base\XmlParser;

class StacAttachmentValidator extends BaseAttachmentValidator
{
    public function __construct(private XmlParser $parser){

    }

    public function validate(array $files){
        $xmlFiles['S'] = $this->getXmlFileName($files, '/^S/');
        $xmlFiles['H'] = $this->getXmlFileName($files, '/^H/');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/^L/');
        $data = $this->parser->parseXML($xmlFiles);
        $unAttachedPacientIDs = $this->getUnAttachedPacientIDs($data['H']);
        $unAttachedPatients = $this->getUnAttachedPatients($data['L']['PERS'], $unAttachedPacientIDs);
        return $unAttachedPatients;
    }

}