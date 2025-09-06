<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\STOMVisitsExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class STOMVisitsExcelUploadController
{
    public function __construct(private STOMVisitsExcelUploader $uploader){}

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $fileName = 'VISITS';
        $file = $_SERVER['DOCUMENT_ROOT'].'/'.$fileName.".xlsx";
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}