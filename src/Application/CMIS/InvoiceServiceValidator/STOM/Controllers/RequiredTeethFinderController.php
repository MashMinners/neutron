<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\RequiredTeethFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequiredTeethFinderController
{
    public function __construct(private RequiredTeethFinder $finder, private ExcelGenerator $generator){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findRequiredTeeth();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС', 'Зуб', 'Диагноз'];
        $this->generator->generate('Отсуствует код зуба', $xlsHeader, $result);
        return new JsonResponse('Количество записей где отсутсвует код зуба, но проставлен диагноз '.count($result));
    }

}