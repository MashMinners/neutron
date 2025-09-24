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

    public function findDuplicates(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->registry->findDuplicates();
        return new JsonResponse($result);
    }

}