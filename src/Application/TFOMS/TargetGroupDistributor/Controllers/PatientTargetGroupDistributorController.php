<?php

namespace Application\TFOMS\TargetGroupDistributor\Controllers;

use Application\TFOMS\TargetGroupDistributor\Models\PatientTargetGroupDistributor;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatientTargetGroupDistributorController
{
    public function __construct(private PatientTargetGroupDistributor $distributor)
    {

    }

    public function distribute(ServerRequestInterface $request) : ResponseInterface{
        $start = microtime(true);
        $result = $this->distributor->distribute();
        $finish = microtime(true) - $start;
        return new JsonResponse($finish);
    }

}