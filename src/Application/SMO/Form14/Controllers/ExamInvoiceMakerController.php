<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\ExamInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExamInvoiceMakerController
{
    public function __construct(private ExamInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/Проф осмотры.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}