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
        $data = $this->uploader->readExcel('TAP');
        return new JsonResponse($data);
    }

}