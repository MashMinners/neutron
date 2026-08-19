<?php

namespace Application\CMIS\AttachmentValidator\Controllers;

use Application\CMIS\AttachmentValidator\Models\DispAttachmentValidator;
use Application\CMIS\AttachmentValidator\Models\ExcelGenerator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispAttachmentValidatorController extends BaseController
{
    public function __construct(private DispAttachmentValidator $validator, private ExcelGenerator $generator)
    {

    }

    public function validate(ServerRequestInterface $request) : ResponseInterface {
        $files = $this->scanDir();
        $result = $this->validator->validate($files);
        $this->generator->generate($result, 'Неприкрепленные [Диспансеризация]');
        return new JsonResponse('Количество непрекрепленных '.count($result));
    }

}