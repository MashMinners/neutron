<?php

declare(strict_types=1);

namespace Application\ExcelUploader\Controllers;

use Application\ExcelUploader\Models\BufferDISPRegistryExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BufferDISPRegistryExcelUploaderController
{
    public function __construct(private BufferDISPRegistryExcelUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        //$params = $request->getQueryParams()['registerType'];
        //$file = 'storage/'.$params.'.xlsx';
        $file = 'storage/DISP.xlsx';
        $result = $this->uploader->excelDataToMySQLData($file);
        return new JsonResponse($result);
    }

}