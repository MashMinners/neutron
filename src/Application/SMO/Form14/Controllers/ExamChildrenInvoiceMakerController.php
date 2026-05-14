<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\ExamChildrenInvoiceMaker;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExamChildrenInvoiceMakerController
{
    public function __construct(private ExamChildrenInvoiceMaker $invoiceMaker){

    }

    public function makeInvoice(ServerRequestInterface $request) : ResponseInterface{
        $journal = 'storage/smo/Проф осмотры несоверш.xlsx';
        $result = $this->invoiceMaker->makeInvoice($journal);
        return new JsonResponse($result);
    }

}