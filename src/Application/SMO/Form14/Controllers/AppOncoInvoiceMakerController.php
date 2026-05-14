<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\AppOncoInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppOncoInvoiceMakerController
{
    public function __construct(private AppOncoInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/Апп онкология.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}