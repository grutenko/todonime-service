<?php


namespace App\Ws;


use Workerman\Connection\ConnectionInterface;

class Subscriber
{
    /**
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * @var string
     */
    public $channel;

    /**
     * @var array
     */
    public $filter;

    /**
     * Subscriber constructor.
     * @param ConnectionInterface $connection
     * @param string $channel
     * @param array $filter
     */
    public function __construct( ConnectionInterface $connection, string $channel, array $filter = [] )
    {
        $this->connection   = $connection;
        $this->channel      = $channel;
        $this->filter       = $filter;
    }
}