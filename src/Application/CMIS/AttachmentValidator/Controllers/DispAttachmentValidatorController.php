<?php

namespace Application\CMIS\AttachmentValidator\Controllers;

use Application\CMIS\AttachmentValidator\Base\BaseController;
use Application\CMIS\AttachmentValidator\Base\ExcelGenerator;
use Application\CMIS\AttachmentValidator\Models\DispAttachmentValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispAttachmentValidatorController extends BaseController
{
    public function __construct(private DispAttachmentValidator $validator, private ExcelGenerator $generator)
    {

    }

    private function getFiles(){
        $files = $this->scanDir();
        $xmlFiles['D'] = $this->getXmlFileName($files, '/\/D/');
        $xmlFiles['F'] = $this->getXmlFileName($files, '/\/F/');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/\/L/');
        return $xmlFiles;
    }

    public function validate(ServerRequestInterface $request) : ResponseInterface {
        $files = $this->getFiles();
        $result = $this->validator->validate($files);
        $this->generator->generate($result, 'Неприкрепленные [Диспансеризация]');
        return new JsonResponse('Количество непрекрепленных '.count($result));
    }

}