<?php

namespace Application\TFOMS\MedicalBillingValidator\Controllers;

use Application\TFOMS\MedicalBillingValidator\Models\PackagesUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PackagesUploaderController
{
    public function __construct(private PackagesUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $start = microtime(true);
        $result = $this->uploader->upload();
        $finish = microtime(true) - $start;
        return new JsonResponse($result);
    }

}