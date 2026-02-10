<?php

namespace Application\FileUploader\Models;

class FileUploader
{
    public function scanDir(){
        $dir = 'storage'; // путь к директории
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if ($file != "." && $file != "..") { // Пропуск ссылок на текущую/родительскую директории
                $result[] = $file;
            }
        }
        return $result;
    }

    public function upload(){

    }

}