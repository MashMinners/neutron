<?php

namespace Application\Invoices\STOM\Controllers;

use Application\Invoices\STOM\Models\IncorrectServicesFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectServicesFinderController
{
    public function __construct(private IncorrectServicesFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findIncorrectServices();
        return new JsonResponse($result);
    }

}