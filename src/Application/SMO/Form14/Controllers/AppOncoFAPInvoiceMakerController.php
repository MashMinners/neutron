<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\AppOncoFAPInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppOncoFAPInvoiceMakerController
{
    public function __construct(private AppOncoFAPInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/АПП_ЗНО ФАП.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}