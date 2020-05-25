<?php

use App\Ws\EventDispatcher;
use Grutenko\Shikimori\Sdk;
use League\Flysystem\Adapter\Local;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phpFastCache\Helper\Psr16Adapter;

$container->set('shikimori_sdk', function () {
    return new Sdk([
        'app_name' => $_ENV['APP_NAME'] ?: 'Applicaton',
        'client_id' => $_ENV['SHIKIMORI_CLIENT_ID'],
        'client_secret' => $_ENV['SHIKIMORI_CLIENT_SECRET']
    ]);
});

$container->set('monolog', function () {
    $log = new Logger('default');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../storage/log/error.log', Logger::NOTICE));

    return $log;
});

$container->set('mongodb', function () {
    $user = $_ENV['MONGO_USERNAME'] ?: 'todonime';
    $password = $_ENV['MONGO_PASSWORD'] ?: 'todonime';
    $host = $_ENV['MONGO_HOST'] ?: 'localhost';
    $database = $_ENV['MONGO_DATABASE'] ?: 'todonime';

    return new MongoDB\Client("mongodb://{$user}:{$password}@{$host}/{$database}", [], [
        'typeMap' => [
            'array' => 'array',
            'document' => 'array',
            'root' => 'array',
        ],
    ]);
});

$container->set('cache', function () {
    return new Psr16Adapter('mongodb', [
        'host' => $_ENV['MONGO_HOST'] ?: '127.0.0.1',
        'port' => $_ENV['MONGO_PORT'] ?: 27017,
        'username' => $_ENV['MONGO_USERNAME'] ?: 'todonime',
        'password' => $_ENV['MONGO_PASSWORD'] ?: 'todonime',
        'timeout' => $_ENV['MONGO_TIMEOUT'] ?: 1
    ]);
});

$container->set('cdn', function () {
    return new League\Flysystem\Filesystem(
        new Local(
            $_ENV['PUBLIC_STORAGE_DIR'] ?: __DIR__ . '/../storage/public',
            LOCK_EX
        )
    );
});

$container->set('event_dispatcher', function( $c ) {
    return new EventDispatcher("tcp://127.0.0.1:9645");
});