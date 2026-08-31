<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\SimultaneousTeethInclusionFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimultaneousTeethInclusionFinderController
{
    public function __construct(private SimultaneousTeethInclusionFinder $finder, private ExcelGenerator $generator){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findSimultaneousTeethInclusion();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС', 'Зуб', 'Диагноз', 'Врач открывший случай'];
        $this->generator->generate('Пересечения на один зуб. Два диагноза', $xlsHeader, $result);
        return new JsonResponse('Количество пересечений на один зуб. Два диагноза '.count($result));
    }

}