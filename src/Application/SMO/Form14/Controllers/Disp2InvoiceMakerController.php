<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\Disp2InvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Disp2InvoiceMakerController
{
    public function __construct(private Disp2InvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/2й этап дисп.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}