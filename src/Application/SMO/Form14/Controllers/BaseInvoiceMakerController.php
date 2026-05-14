<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\BaseInvoicesMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BaseInvoiceMakerController
{
    public function __construct(private BaseInvoicesMaker $invoicesMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/Профы.xlsx';
        $result = $this->invoicesMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}