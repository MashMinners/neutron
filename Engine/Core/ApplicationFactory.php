<?php

namespace Engine\Core;

use Engine\Application;
use Engine\Router\IRouter;
use Psr\Container\ContainerInterface;

class ApplicationFactory
{
    public static function create(ContainerInterface $container, IRouter $router){
        return new Application($container, $router);
    }

}