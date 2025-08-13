<?php
#1. Автозагрузка классов
chdir(dirname(__DIR__));
require 'vendor/autoload.php';
#2. Инициализация DI контейнера
$container = \Engine\Core\ContainerFactory::create();
#3. Создание приложения
$app = $container->get('Application');
#4. Создание запроса
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
#5. Запуск
$app->run($request);