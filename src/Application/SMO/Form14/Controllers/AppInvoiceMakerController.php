<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\AppInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppInvoiceMakerController
{
    public function __construct(private AppInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/АПП.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}