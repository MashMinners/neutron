<?php

namespace Application\XMLParser\Controllers;

use Application\XMLParser\Models\XMLParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XMLParserController
{
    public function __construct(private XMLParser $parser){

    }

    public function parse(ServerRequestInterface $request) : ResponseInterface{
        $result = $this->parser->parse();
    }

}