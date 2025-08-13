<?php

namespace Engine\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IRouter
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}