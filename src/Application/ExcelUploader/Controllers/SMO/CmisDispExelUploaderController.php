<?php

namespace Application\ExcelUploader\Controllers\SMO;

use Application\ExcelUploader\Models\SMO\CmisDispExcelUploader;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CmisDispExelUploaderController
{
    public function __construct(private CmisDispExcelUploader $uploader){

    }

    public function upload(ServerRequestInterface $request) : ResponseInterface{
        //$file = 'storage/CMIS_DISP.xlsx';
        $result = $this->uploader->excelDataToMySQLData();
        return new JsonResponse($result);
    }

}