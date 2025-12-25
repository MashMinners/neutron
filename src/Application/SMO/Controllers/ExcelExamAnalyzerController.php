<?php

namespace Application\SMO\Controllers;

use Application\SMO\Models\ExcelExamAnalyzer;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExcelExamAnalyzerController
{
    public function __construct(private ExcelExamAnalyzer $analyzer){

    }

    public function analyze(ServerRequestInterface $request) : ResponseInterface{
        $bank = 'storage/Bank.xlsx';
        $journal = 'storage/Journal.xlsx';
        $result = $this->analyzer->analyze($bank, $journal);
        return new JsonResponse($result);
    }

}