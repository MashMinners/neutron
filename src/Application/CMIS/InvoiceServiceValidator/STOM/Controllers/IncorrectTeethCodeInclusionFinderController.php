<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\IncorrectTeethCodeInclusionFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectTeethCodeInclusionFinderController
{
    public function __construct(private IncorrectTeethCodeInclusionFinder $finder, private ExcelGenerator $generator){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findIncorrectTeethCodeInclusion();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС', 'Зуб', 'Диагноз'];
        $this->generator->generate('Присутсвует код зуба. Не должен присутсвовать', $xlsHeader, $result);
        return new JsonResponse('Количество записей где присуствует код зуба, хотя должен отсутствовать '.count($result));
    }

}