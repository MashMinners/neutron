<?php

namespace Application\CMIS\InvoiceServiceValidator\DISP\Controllers;

use Application\CMIS\InvoiceServiceValidator\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\DISP\Models\IntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IntersectionsFinderController
{
    public function __construct(private IntersectionsFinder $finder){

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
    public function find(ServerRequestInterface $request) : ResponseInterface{
        $files = $this->scanDir();
        $result = $this->finder->findIntersectionsWithStac($files);
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'Полис', 'Дата открытия диспы',
            'Дата закрытия диспы', 'Дата стационара'];
        (new ExcelGenerator())->generate('Пересечения по диспансеризации и стационару', $xlsHeader, $result);
        return new JsonResponse($result);
    }

}