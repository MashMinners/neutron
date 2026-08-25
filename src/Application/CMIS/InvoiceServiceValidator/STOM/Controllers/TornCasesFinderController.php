<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\TornCasesFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TornCasesFinderController
{
    public function __construct(private TornCasesFinder $finder, private ExcelGenerator $generator){

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findTornCases();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС'];
        $this->generator->generate('Разорванные случаи', $xlsHeader, $result);
        return new JsonResponse('Количество разорванных случаев '.count($result));
    }

}