<?php

namespace Application\FileUploader\Controllers;

use Application\FileUploader\Models\FileUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FileUploaderController
{
    public function __construct(private FileUploader $uploader){

    }
    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->uploader->scanDir();
        return new JsonResponse($result);
    }

}