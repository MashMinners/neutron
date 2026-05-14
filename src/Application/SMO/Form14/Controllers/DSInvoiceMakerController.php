<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\DSInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DSInvoiceMakerController
{
    public function __construct(private DSInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/ТП ОМС-ДСТАЦ.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}