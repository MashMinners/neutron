<?php

namespace Application\CMIS\AttachmentValidator\Controllers;


class BaseController
{
    public function scanDir(){
        $dir = 'storage/cmis/attachment/'; // путь к директории
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if ($file != "." && $file != "..") { // Пропуск ссылок на текущую/родительскую директории
                $result[] = $file;
            }
        }
        return $result;
    }

}