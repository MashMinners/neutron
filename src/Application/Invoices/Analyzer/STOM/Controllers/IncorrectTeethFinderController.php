<?php

namespace Application\Invoices\Analyzer\STOM\Controllers;

use Application\Invoices\Analyzer\STOM\Models\IncorrectTeethFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectTeethFinderController
{
    public function __construct(private IncorrectTeethFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findIncorrectTeeth();
        return new JsonResponse($result);
    }

}