<?php

namespace Application\Invoices\Uploader\STOM\Controllers;

use Application\Invoices\Uploader\STOM\Models\ExcelUploader;
use Application\XlsParser\ExcelParser;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExcelUploaderController
{
    public function __construct(private ExcelUploader $uploader){

    }

    public function uploadToMySql(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/file.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}