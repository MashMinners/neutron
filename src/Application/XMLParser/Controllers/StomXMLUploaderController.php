<?php

namespace Application\XMLParser\Controllers;

use Application\XMLParser\Models\StomXMLUploader;
use Application\XMLParser\Models\XMLParser;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StomXMLUploaderController
{
    public function __construct(private StomXMLUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $xmlData = (new XMLParser())->parse();
        $result = $this->uploader->upload($xmlData);
        return new JsonResponse($result);
    }

}