<?php
use App\Lib\Queue\Daemon;
use App\Worker\BumpEpisodeWorker;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

$container = new Container();
require __DIR__ . '/../src/dependencies.php';

$daemon = new Daemon(
    $container->get('mongodb')->todonime->queue,
    [
        'bump_episode' => new BumpEpisodeWorker($container)
    ]
);
$daemon->run();

