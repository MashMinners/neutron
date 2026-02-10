<?php

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\SickNoteIntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SickNoteIntersectionsFinderController
{
    public function __construct(private SickNoteIntersectionsFinder $finder){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        return new JsonResponse($result);
    }

}