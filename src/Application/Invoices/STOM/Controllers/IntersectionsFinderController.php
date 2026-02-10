<?php

namespace Application\Invoices\STOM\Controllers;

use Application\Invoices\STOM\Models\IntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IntersectionsFinderController
{
    public function __construct(private IntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findSortedIntersections();
        return new JsonResponse($result);
    }

}