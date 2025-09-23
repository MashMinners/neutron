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

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/VISITS.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function truncate(ServerRequestInterface $request) : ResponseInterface{
        $this->uploader->truncate();
        return new JsonResponse('Таблица очищена');
    }

}