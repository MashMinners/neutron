<?php

namespace Application\InvoiceRegisterAnalyzer\Controllers;

use Application\InvoiceRegisterAnalyzer\Models\StomRegisterAnalyzer;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StomRegisterAnalyzerController
{
    public function __construct(private StomRegisterAnalyzer $analyzer){

    }

    public function findIncorrectPurpose(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->analyzer->findIncorrectPurpose();
        return new JsonResponse($result);
    }

    public function findIncorrectTeeth(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->analyzer->findIncorrectTeeth();
        //return new JsonResponse($result);
        $response = new JsonResponse($result);
        return  $response;
    }

    public function findIncorrectServices(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->analyzer->findIncorrectServices();
        return new JsonResponse($result);
    }

}