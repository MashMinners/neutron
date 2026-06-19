<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Models;

class DPInvoiceValidator extends BaseDispValidator
{
    public function __construct(private BaseInvoiceXmlParser $parser){}
    public function validate(array $files){
        $data = $this->parser->parseXML($files);
        return $data;
    }

}