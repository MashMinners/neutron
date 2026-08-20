<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Models\IntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IntersectionsFinderController
{
    public function __construct(private IntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findIntersections();
        return new JsonResponse('Количество пересечений '.$result);
    }

}