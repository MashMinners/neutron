<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Controllers;

use Application\CMIS\InvoiceServiceValidator\DISP\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\DISP\Models\DPInvoiceValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DPInvoiceValidatorController
{
    public function __construct(private DPInvoiceValidator $validator, private ExcelGenerator $generator)
    {

    }

    public function scanDir(){
        $dir = 'storage/cmis'; // путь к директории
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if ($file != "." && $file != "..") { // Пропуск ссылок на текущую/родительскую директории
                $result[] = $file;
            }
        }
        return $result;
    }
    public function validate(ServerRequestInterface $request) : ResponseInterface{
        $files = $this->scanDir();
        $result = $this->validator->validate($files);
        $xlsHeader = ['ID_PAC', 'Фамилия', 'Имя', 'Отчество', 'Пол', 'Дата рождения', 'СНИЛС', 'ОКАТО 1', 'ОКАТО 2', 'Возвраст'];
        $this->generator->generate('Диспансеризация 1 этап. Ошибки валидации', $xlsHeader, $result);
        return new JsonResponse('Количество случаев с ошибками по услугам '.count($result));

    }

}