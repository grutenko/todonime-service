<?php

use App\Ws\Channel\CommentsChannel;
use App\Ws\EventObserver;
use App\Ws\Server;
use Workerman\Worker;

require __DIR__ . '/../vendor/autoload.php';


(new EventObserver(
    'tcp://0.0.0.0:9645'
))->enable();

$ws = (new Server(
    'websocket://0.0.0.0:9999',
    'tcp://127.0.0.1:9645',
    4
));
$ws->addChannel(new CommentsChannel);
$ws->enable();

Worker::runAll();