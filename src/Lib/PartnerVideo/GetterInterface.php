<?php


namespace App\Lib\PartnerVideo;

use Generator;
use MongoDB\Database;

/**
 * Interface GetterInterface
 * @package App\Lib\PartnerVideo
 */
interface GetterInterface
{
    /**
     * Должен вернуть название сервиса с которого выбирается видео.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Должен вернуть последний ID видео партнера, который будет записан.
     *
     * @return int
     */
    public function getLastId(): int;

    /**
     * Должен вернуть генератор для получения видео.
     *
     * @param Database $db
     * @return Generator
     */
    public function videoGenerator(Database $db): Generator;
}