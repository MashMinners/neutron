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

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $fileName = 'REESTR';
        $file = $_SERVER['DOCUMENT_ROOT'].'/'.$fileName.".xlsx";
        $result = $this->uploader->excelDataToMySQLData($file, 'STOM');
        return new JsonResponse($result);
    }

}