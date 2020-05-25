<?php


namespace App\Ws;


use Workerman\Worker;

class EventObserver
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var Worker
     */
    protected $server;

    /**
     * EventObserver constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return void
     */
    public function enable()
    {
        $this->server = new Worker($this->url);
        $this->server->onMessage = function( $from, $data )
        {
            foreach( $this->server->connections as $id => $connection)
            {
                if( $from->id != $id )
                {
                    $connection->send( $data );
                }
            }
        };
    }
}