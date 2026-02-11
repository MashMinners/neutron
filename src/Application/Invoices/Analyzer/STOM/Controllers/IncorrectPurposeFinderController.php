<?php

namespace Application\Invoices\Analyzer\STOM\Controllers;

use Application\Invoices\Analyzer\STOM\Models\IncorrectPurposeFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectPurposeFinderController
{
    public function __construct(private IncorrectPurposeFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findIncorrectPurpose();
        return new JsonResponse($result);
    }

}