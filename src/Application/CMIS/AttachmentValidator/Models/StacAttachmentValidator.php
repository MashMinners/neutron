<?php

namespace Application\CMIS\AttachmentValidator\Models;

class StacAttachmentValidator extends BaseAttachmentValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){

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