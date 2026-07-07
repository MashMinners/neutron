<?php

namespace Application\TFOMS\MedicalBillingValidator\STAC\Controllers;

use Application\TFOMS\MedicalBillingValidator\STAC\Models\GuaranteedPaymentsMatcher;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GuaranteedPaymentsMatcherController
{
    public function __construct(private GuaranteedPaymentsMatcher $matcher){

    }

    public function match(ServerRequestInterface $request) : ResponseInterface{
        $start = microtime(true);
        $result = $this->matcher->match();
        $finish = microtime(true) - $start;
        return new JsonResponse($result);
    }
}