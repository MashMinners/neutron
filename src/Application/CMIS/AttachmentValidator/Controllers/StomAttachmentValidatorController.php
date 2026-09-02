<?php

namespace Application\CMIS\AttachmentValidator\Controllers;

use Application\CMIS\AttachmentValidator\Base\BaseController;
use Application\CMIS\AttachmentValidator\Base\ExcelGenerator;
use Application\CMIS\AttachmentValidator\Models\StomAttachmentValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StomAttachmentValidatorController extends BaseController
{
    public function __construct(private StomAttachmentValidator $validator, private ExcelGenerator $generator)
    {

    }

    private function getFiles(){
        $files = $this->scanDir();
        $xmlFiles['P'] = $this->getXmlFileName($files, '/\/P/');
        $xmlFiles['H'] = $this->getXmlFileName($files, '/\/H/');
        $xmlFiles['L'] = $this->getXmlFileName($files, '/\/L/');
        return $xmlFiles;
    }

    public function validate(ServerRequestInterface $request) : ResponseInterface {
        $files = $this->getFiles();
        $result = $this->validator->validate($files);
        $this->generator->generate($result, 'Неприкрепленные [Стоматология]');
        return new JsonResponse('Количество непрекрепленных '.count($result));
    }

}