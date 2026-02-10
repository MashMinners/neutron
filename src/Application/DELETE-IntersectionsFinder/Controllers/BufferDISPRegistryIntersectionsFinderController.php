<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\BufferDISPRegistryIntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BufferDISPRegistryIntersectionsFinderController
{
    public function __construct(private BufferDISPRegistryIntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        return new JsonResponse($result);
    }

}