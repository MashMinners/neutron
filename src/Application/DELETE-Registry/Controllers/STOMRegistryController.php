<?php

namespace Application\Registry\Controllers;

use Application\Registry\Models\STOMRegistry;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class STOMRegistryController
{
    public function __construct(private STOMRegistry $registry){

    }

    public function findTornCases(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->registry->findTornCases();
        return new JsonResponse($result);
    }

}