<?php

namespace Engine\DTO;

class StructuredResponse implements \JsonSerializable
{
    private $code =  200;
    private $message = 'Standard message';
    private $body = [];

    public function __get($name){
        return $this->$name;
    }
    public function __set($name, $value){
        $this->$name = $value;
    }

    public function setBody($key, $value){
        $this->body[$key] = $value;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'Code' => $this->code,
            'Message' => $this->message,
            'Body' => $this->body
        ];
    }
}