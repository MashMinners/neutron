<?php

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\SickNoteExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SickNoteExcelUploaderController
{
    public function __construct(private SickNoteExcelUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        $file = 'storage/LN.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

    public function truncate(ServerRequestInterface $request) : ResponseInterface{
        $this->uploader->truncate();
        return new JsonResponse('Таблица буфер по ЛН очищена');
    }

}