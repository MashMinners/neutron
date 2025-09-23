<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\STOMRegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class STOMRegisterExcelUploadController
{
    public function __construct(private STOMRegisterExcelUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/STOM.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}