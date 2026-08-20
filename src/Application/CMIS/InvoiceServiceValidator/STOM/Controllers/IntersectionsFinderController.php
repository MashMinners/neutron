<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\IntersectionsFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IntersectionsFinderController
{
    public function __construct(private IntersectionsFinder $finder, private  ExcelGenerator $generator){

    }
    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findIntersections();
        $xlsHeader = ['Дата начала (ФОНД)', 'Дата окнчания (ФОНД)', 'Дата начала (РЕЕСТР)', 'Дата окончания (РЕЕСТР)',
            'Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'Полис', 'СНИЛС'];
        $this->generator->generate('Пересчения с фондом', $xlsHeader, $result);
        return new JsonResponse('Количество пересечений '.count($result));
    }

}