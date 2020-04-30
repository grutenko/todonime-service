<?php


namespace App\Action;


use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class Action
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Action constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!$this->__isset($name)) {
            return null;
        }
        return $this->container->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        return $this->container->has($name);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public abstract function __invoke(Request $request, Response $response, array $args): Response;
}