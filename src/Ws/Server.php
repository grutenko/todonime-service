<?php


namespace App\Ws;

use App\Ws\Channel\ChannelInterface;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

class Server
{
    /**
     * @var Worker
     */
    protected $server;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $eventObserverUrl;

    /**
     * @var int
     */
    protected $countWorkers;

    /**
     * @var array|null
     */
    protected $ssl = null;

    /**
     * @var array
     */
    private $subscribers = [];

    /**
     * @var array<ChannelInterface>
     */
    private $channels = [];

    /**
     * Server constructor.
     * @param string $url
     * @param string $eventObserverUrl
     * @param int $countWorkers
     * @param array|null $ssl
     */
    public function __construct( string $url, string $eventObserverUrl, int $countWorkers = 1, ?array $ssl = null )
    {
        $this->url              = $url;
        $this->eventObserverUrl = $eventObserverUrl;
        $this->countWorkers     = $countWorkers;
        $this->ssl              = $ssl;
    }

    /**
     * @param ChannelInterface $channel
     * @return void
     */
    public function addChannel( ChannelInterface $channel )
    {
        $this->channels[ $channel->getName() ] = $channel;
    }

    /**
     * @param array $channels
     * @return void
     */
    public function addChannels( array $channels )
    {
        foreach($channels as $channel)
        {
            $this->addChannel($channel);
        }
    }

    /**
     * @return void;
     */
    public function enable()
    {
        $this->server = new Worker(
            $this->url,
            ($this->useSsl() ? [ 'ssl' => $this->ssl ] : [])
        );
        $this->server->count = $this->countWorkers;

        if( $this->useSsl() )
        {
            $this->server->transport = 'ssl';
        }

        $this->addListeners();
    }

    /**
     * @param ConnectionInterface $connection
     * @param string $channel
     * @param array $filter
     */
    private function addSubscriber( ConnectionInterface $connection, string $channel, array $filter )
    {
        $this->subscribers[ $connection->id ] = new Subscriber($connection, $channel, $filter);
    }

    /**
     * @param ConnectionInterface $connection
     */
    private function deleteSubscriber( ConnectionInterface $connection )
    {
        if( isset($this->subscribers[ $connection->id ]) )
        {
            unset($this->subscribers[ $connection->id ]);
        }
    }

    /**
     * @param array $data
     */
    private function forwardData( array $data )
    {
        if( isset( $data['channel'] ) && isset( $this->channels[ $data['channel'] ] ))
        {
            $subscribers = $this
                ->channels[ $data['channel'] ]
                ->chooseSubscribers( $this->subscribers, $data );

            foreach($subscribers as $subscriber)
            {
                $subscriber->connection->send(json_encode($data));
            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function connectToEventObserver()
    {
        $connection = new AsyncTcpConnection($this->eventObserverUrl);
        $connection->onMessage = function( $connection, $data )
        {
            $this->forwardData( json_decode($data, true) );
        };
        $connection->connect();
    }

    /**
     * Подключает необходимые обработчики и случает подключения.
     * @return void
     */
    private function addListeners()
    {
        $this->server->onWebSocketConnect = function($connection)
        {
            $this->addSubscriber( $connection, $_GET['channel'], $_GET['filter'] );
        };
        $this->server->onClose = function( $connection )
        {
            $this->deleteSubscriber( $connection );
        };
        $this->server->onWorkerStart = function()
        {
            $this->connectToEventObserver();
            $this->setPingTimer();
        };
    }

    /**
     * @return void
     */
    private function setPingTimer()
    {
        Timer::add(10, function() {
            foreach($this->server->connections as $connection) {
                $connection->send(pack('H*', '890400000000'), true);
            }
        });
    }

    /**
     * @return bool;
     */
    private function useSsl()
    {
        return is_array($this->ssl);
    }
}