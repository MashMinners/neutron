<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\VisitsExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VisitsExcelUploadController
{
    public function __construct(private VisitsExcelUploader $uploader){}

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/VISITS.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function truncate(ServerRequestInterface $request) : ResponseInterface{
        $this->uploader->truncate();
        return new JsonResponse('Таблица VISITS очищена');
    }

}