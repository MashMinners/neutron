<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\BufferSTOMRegistryIntersectionsFinder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BufferSTOMRegistryIntersectionsFinderController
{
    public function __construct(private BufferSTOMRegistryIntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        return new JsonResponse($result);
    }

    public function findIncorrectPurposes(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->findIncorrectPurposeDevided();
        $incorrect['single'] = $result['single']['incorrect'];
        $incorrect['multi'] = $result['multi']['incorrect'];
        $merged = array_merge($incorrect['single'], $incorrect['multi']);
        return new JsonResponse($merged);
    }

}