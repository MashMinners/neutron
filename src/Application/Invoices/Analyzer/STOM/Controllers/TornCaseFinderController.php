<?php

namespace Application\Invoices\Analyzer\STOM\Controllers;

use Application\Invoices\Analyzer\STOM\Models\TornCaseFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TornCaseFinderController
{
    public function __construct(private TornCaseFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findTornCases();
        return new JsonResponse($result);
    }

}