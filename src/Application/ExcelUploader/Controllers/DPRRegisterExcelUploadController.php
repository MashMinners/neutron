<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\DPRRegisterExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Загрузка 2 эатпа диспансеризации
 */
class DPRRegisterExcelUploadController
{
    public function __construct(private DPRRegisterExcelUploader $uploader){

    }

    public function index(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/DPR.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}