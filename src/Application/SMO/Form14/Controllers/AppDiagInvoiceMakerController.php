<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\AppDiagInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppDiagInvoiceMakerController
{
    public function __construct(private AppDiagInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/АПП(диагност услуги).xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}