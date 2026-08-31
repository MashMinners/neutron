<?php

namespace Application\CMIS\AttachmentValidator\Base;


class BaseController
{
    private string $directory = 'storage/cmis/';
    protected function scanDir(){
        $files = glob($this->directory . '*.xml');
        return $files;
    }

    protected function getXmlFileName(array $files, string $pattern){
        $filtered = array_filter($files, function($item) use ($pattern){
            return preg_match($pattern, $item);
        });
        return ((string)reset($filtered));
    }

}