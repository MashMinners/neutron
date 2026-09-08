<?php

namespace Application\CMIS\InvoiceServiceValidator\STOM\Controllers;

use Application\CMIS\InvoiceServiceValidator\STOM\Base\ExcelGenerator;
use Application\CMIS\InvoiceServiceValidator\STOM\Models\IncorrectPurposeFinder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IncorrectPurposeFinderController
{
    public function __construct(private IncorrectPurposeFinder $finder, private ExcelGenerator $generator)
    {

    }

    public function find(ServerRequestInterface $request) : ResponseInterface {
        $result = $this->finder->findIncorrectPurposes();
        $xlsHeader = ['Дата начала', 'Дата окончания', 'Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'Полис', 'СНИЛС',
            'Цель', 'Диагноз'];
        $this->generator->generate('Некорректные цели', $xlsHeader, $result);
        return new JsonResponse('Количество пересечений '.count($result));
    }

}