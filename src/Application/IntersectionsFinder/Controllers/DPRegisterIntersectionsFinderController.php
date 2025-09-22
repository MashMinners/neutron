<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\DPRegisterIntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DPRegisterIntersectionsFinderController
{
    public function __construct(private DPRegisterIntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        return new JsonResponse($result);
    }

}