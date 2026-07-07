<?php

namespace Application\TFOMS\MedicalBillingValidator\STAC\Controllers;

use Application\TFOMS\MedicalBillingValidator\STAC\Models\Validator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ValidatorController
{
    public function __construct(private Validator $validator){

    }

    public function validate(ServerRequestInterface $request) : ResponseInterface{
        $start = microtime(true);
        $result = $this->validator->validate();
        $finish = microtime(true) - $start;
        return new JsonResponse($result);
    }

}