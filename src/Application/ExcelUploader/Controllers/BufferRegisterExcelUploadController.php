<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\BufferRegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Загрузка проф. осмотров 21 цель
 */
class BufferRegisterExcelUploadController
{
    public function __construct(private BufferRegisterExcelUploader $uploader){

    }
    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = $request->getQueryParams()['registerType'].'.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function truncate(ServerRequestInterface $request) : ResponseInterface{
        $this->uploader->truncate();
        return new JsonResponse('Таблица очищена');
    }

}