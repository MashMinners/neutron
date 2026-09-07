<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\SameSpecialtiesFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SameSpecialtiesFinderController
{
    public function __construct(private SameSpecialtiesFinder $finder, private ExcelGenerator $generator){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findSameSpecialties();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС', 'Врач открывший случай', 'Профиль',
            'PRVS',  'Врач совместитель',  'Услуга',  'Диагноз',  'Профиль совместителя',  'PRVS совместителя',];
        $this->generator->generate('Совмещения по одной специальности', $xlsHeader, $result);
        return new JsonResponse('Количество совмещений '.count($result));
    }

}