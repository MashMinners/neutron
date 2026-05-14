<?php

namespace Application\SMO\Controllers;

use Application\SMO\Models\ExcelKSAnalyzer;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExcelKSAnalyzerController
{
    public function __construct(private ExcelKSAnalyzer $analyzer)
    {

    }

    public function analyze(ServerRequestInterface $request) : ResponseInterface{
        $bank = 'storage/smo/ksBank.xlsx';
        $journal = 'storage/smo/ksJournal.xlsx';
        $result = $this->analyzer->analyze($bank, $journal);
        return new JsonResponse($result);
    }

}