<?php

namespace Application\XMLParser\Models;

use Engine\Database\IConnector;

class XMLUploader
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function PMUpload(){

    }

    private function LMUpload($LM){

    }

}