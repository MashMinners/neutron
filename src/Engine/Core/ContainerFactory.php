<?php

declare(strict_types=1);

namespace Engine\Core;

use DI\ContainerBuilder;
use League\Container\Container;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    private static function createPhpDi() : ContainerInterface{
        $definitions = require 'configs/dependencies.php';
        $builder = new ContainerBuilder();
        $builder->addDefinitions($definitions);
        return $builder->build();
    }

    private static function createLeagueDi() : ContainerInterface {
        return new Container();
    }

    public static function create(string $name = null) : ContainerInterface {
        switch ($name) {
            case 'PHP DI' : return self::createPhpDi();
            case 'League DI' : return self::createLeagueDi();
            default : return self::createPhpDi();
        }
    }
}