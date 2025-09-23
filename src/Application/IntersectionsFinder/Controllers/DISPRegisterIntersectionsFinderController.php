<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\DISPRegisterIntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DISPRegisterIntersectionsFinderController
{
    public function __construct(private DISPRegisterIntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        return new JsonResponse($result);
    }

}