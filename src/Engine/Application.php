<?php

namespace Engine;

use Engine\Router\IRouter;
use League\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
    public function __construct(private ContainerInterface $container, private IRouter $router)
    {
        require 'configs/routes.php';
        require 'configs/middlewares.php';
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function get(string $path, $handler) : Route
    {
        return $this->router->get( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function post(string $path, $handler) : Route
    {
        return $this->router->post( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function put(string $path, $handler) : Route
    {
        return $this->router->put( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function delete(string $path, $handler) : Route
    {
        return $this->router->delete( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function patch(string $path, $handler) : Route
    {
        return $this->router->patch( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function head(string $path, $handler) : Route
    {
        return $this->router->head( $path, $handler);
    }

    /**
     * @param string $path
     * @param $handler
     * @return Route
     */
    private function options(string $path, $handler) : Route
    {
        return $this->router->options( $path, $handler);
    }

    /**
     * @param string $middleware
     * @return \League\Route\Middleware\MiddlewareAwareInterface
     */
    private function lazyMiddleware(string $middleware) {
       return $this->router->lazyMiddleware($middleware);
    }

    /**
     * @param array $middlewares
     * @return \League\Route\Middleware\MiddlewareAwareInterface
     */
    private function lazyMiddlewares(array $middlewares) {
        return $this->router->lazyMiddlewares($middlewares);
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function run(ServerRequestInterface $request){
        $response = $this->router->dispatch($request);
        $emitter = $this->container->get('Emitter');
        $emitter->emit($response);
    }

}