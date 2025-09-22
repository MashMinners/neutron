<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\DORegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Загрузка проф. осмотров 21 цель
 */
class DORegisterExcelUploadController
{
    public function __construct(private DORegisterExcelUploader $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/DO.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}