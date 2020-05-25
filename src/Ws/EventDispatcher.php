<?php


namespace App\Ws;

use Monolog\Logger;
use Workerman\Connection\TcpConnection;

class EventDispatcher
{
    /**
     * @var resource
     */
    private $client = false;

    /**
     * EventDispatcher constructor.
     * @param string $url
     * @throws \Exception
     */
    public function __construct( string $url )
    {
        $arUrl = parse_url ( $url );

        $errno = 0;
        $errstr = '';
        $this->client = fsockopen($arUrl['host'], $arUrl['port'], $errno, $errstr);
        if($errno != 0) {
            throw new \Exception("SOCK: {$errstr}");
        }
    }

    /**
     * @param string $channel
     * @param string $action
     * @param array $data
     * @return bool|null
     */
    public function send(string $channel, string $action, array $data)
    {
        if( $this->client == false)
        {
            return false;
        }
        $result = [
            'channel' => $channel,
            'action' => $action,
            'eventData' => $data
        ];
        return fwrite($this->client, json_encode( $result ) );
    }
}