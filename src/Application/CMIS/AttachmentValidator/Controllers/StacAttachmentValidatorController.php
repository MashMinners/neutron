<?php

namespace Application\CMIS\AttachmentValidator\Controllers;

use Application\CMIS\AttachmentValidator\Base\BaseController;
use Application\CMIS\AttachmentValidator\Base\ExcelGenerator;
use Application\CMIS\AttachmentValidator\Models\StacAttachmentValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StacAttachmentValidatorController extends BaseController
{
    public function __construct(private StacAttachmentValidator $validator, private ExcelGenerator $generator)
    {

    }

    public function validate(ServerRequestInterface $request) : ResponseInterface {
        $files = $this->scanDir();
        $result = $this->validator->validate($files);
        $this->generator->generate($result, 'Неприкрепленные [Стационар]');
        return new JsonResponse('Количество непрекрепленных '.count($result));
    }

}