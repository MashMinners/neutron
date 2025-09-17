<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\DPRegisterExcelUploader;
use Application\ExcelUploader\Models\STOMRegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DPRegisterExcelUploadController
{
    public function __construct(private DPRegisterExcelUploader $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/DP.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}