<?php

declare(strict_types=1);

namespace Engine\Core;

use Engine\Router\LeagueRouterDecorator;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Container\ContainerInterface;

class RouterFactory
{
    public static function create(ContainerInterface $container){
        $strategy = (new ApplicationStrategy())->setContainer($container);
        $router = (new LeagueRouterDecorator())->setStrategy($strategy);
        return $router;
    }

}