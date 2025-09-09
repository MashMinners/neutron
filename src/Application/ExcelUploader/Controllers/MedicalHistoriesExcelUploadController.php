<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\MedicalHistoriesExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MedicalHistoriesExcelUploadController
{
    public function __construct(private MedicalHistoriesExcelUploader $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/IB.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}