<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\DVRegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Загрузка 2 эатпа диспансеризации
 */
class DVRegisterExcelUploadController
{
    public function __construct(private DVRegisterExcelUploader $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/DV.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}