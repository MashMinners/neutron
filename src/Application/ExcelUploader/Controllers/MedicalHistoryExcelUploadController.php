<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\MedicalHistoryExcelUpload;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MedicalHistoryExcelUploadController
{
    public function __construct(private MedicalHistoryExcelUpload $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface {
        $fileName = 'IB';
        $file = $_SERVER['DOCUMENT_ROOT'].'/'.$fileName.".xlsx";
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function getMedicalHistories(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->uploader->getMedicalHistories();
        return new JsonResponse($result);
    }

}