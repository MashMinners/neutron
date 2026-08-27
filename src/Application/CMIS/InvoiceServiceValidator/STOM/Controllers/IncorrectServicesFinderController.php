<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\IncorrectServicesFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectServicesFinderController
{
    public function __construct(private IncorrectServicesFinder $finder, private ExcelGenerator $generator)
    {

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findIncorrectServices();
        $xlsHeader = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'СНИЛС'];
        $this->generator->generate('Две и более первичных', $xlsHeader, $result['twoOrMorePrimary']);
        $this->generator->generate('Ниодной первичной', $xlsHeader, $result['haveNoPrimary']);
        return new JsonResponse('Две и более первичных  '.count($result['twoOrMorePrimary']).' Ниодной первичной  '.count($result['haveNoPrimary']));
    }

}