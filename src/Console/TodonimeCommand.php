<?php


namespace App\Console;


use DI\Container;

abstract class TodonimeCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Command constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }
}