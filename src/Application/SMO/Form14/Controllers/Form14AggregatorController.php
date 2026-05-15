<?php

namespace Application\SMO\Form14\Controllers;

use Application\SMO\Form14\Models\Form14Aggregator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Form14AggregatorController
{
    public function __construct(private Form14Aggregator $aggregator){

    }

    public function aggregate(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->aggregator->aggregate();
        return new JsonResponse($result);
    }

}