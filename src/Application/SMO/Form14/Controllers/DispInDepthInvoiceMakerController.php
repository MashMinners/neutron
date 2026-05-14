<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\DispInDepthInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispInDepthInvoiceMakerController
{
    public function __construct(private DispInDepthInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/1й этап углуб дисп.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }


}