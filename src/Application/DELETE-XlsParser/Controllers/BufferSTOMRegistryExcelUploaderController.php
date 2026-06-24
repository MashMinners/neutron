<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\BufferSTOMRegistryExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BufferSTOMRegistryExcelUploaderController
{
    public function __construct(private BufferSTOMRegistryExcelUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/STOM.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function truncate(ServerRequestInterface $request) : ResponseInterface{
        $this->uploader->truncate();
        return new JsonResponse('Таблица буфер по стоматологии очищена');
    }

}