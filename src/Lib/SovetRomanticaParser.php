<?php


namespace App\Lib;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\Database;

class SovetRomanticaParser
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * GetVideos constructor.
     * @param Container $container
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Container $container)
    {
        $this->db = $container->get('mongodb')->todonime;
        $this->sdk = $container->get('shikimori_sdk');
    }

    /**
     * Возвращает генератор для получения id советромантики вместе с ID shikimori.
     * Это нужно для выборки видео с sovetromantica.com
     *
     * @return \Generator
     */
    public function parseIdGenerator(): \Generator
    {

    }
}