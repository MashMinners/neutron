<?php

namespace Application\Invoices\Uploader\STOM\Controllers;

use Application\Invoices\Uploader\STOM\Models\XmlParser;
use Application\Invoices\Uploader\STOM\Models\XmlUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XmlUploaderController
{
    public function __construct(private XmlUploader $uploader){

    }

    public function uploadToMySql(ServerRequestInterface $request) : ResponseInterface{
        $files = ['HM250500T25_260150005', 'LM250500T25_260150005', 'PM250500T25_260150005'];
        $xmlData = (new XmlParser())->parseXML($files);
        $result = $this->uploader->xmlDataToMySQLData($xmlData);
        return new JsonResponse($result);
    }

}