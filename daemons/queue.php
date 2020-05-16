<?php
use App\Lib\Queue\Daemon;
use App\Worker\BumpEpisodeWorker;
use DI\Container;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv
    ::createImmutable(__DIR__ . '/../')
    ->load();

$container = new Container();
require __DIR__ . '/../src/dependencies.php';

(new Daemon(
    $container
        ->get('mongodb')
        ->todonime
        ->queue,
    [
        'bump_episode' => new BumpEpisodeWorker($container)
    ]
))->run();

