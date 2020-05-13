<?php


namespace App\Lib\Queue;


interface WorkerInterface
{
    /**
     * Обрабатывает $job и возвращает true или false в зависимости
     *
     * @param array $job
     * @return bool
     */
    public function handle( array $job ): bool;
}