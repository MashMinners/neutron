<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\DprInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DprInvoiceMakerController
{
    public function __construct(private DprInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/Дисп.Репродуктивное здоровье 1 этап.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}