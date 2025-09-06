<?php

declare(strict_types=1);

namespace Application\IntersectionsFinder\Controllers;

use Application\IntersectionsFinder\Models\STOMRegisterIntersectionsFinder;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class STOMRegisterIntersectionsFinderController
{
    public function __construct(private STOMRegisterIntersectionsFinder $finder){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->finder->find();
        $response = require_once ('src/Application/Views/intersections.php');// 'src/Application/Views/intersections.php';
        //$response = file_get_contents('src/Application/Views/intersections.php');// 'src/Application/Views/intersections.php';

        //return new JsonResponse($result);
        return new HtmlResponse($response);
    }

}