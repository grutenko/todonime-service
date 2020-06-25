<?php


namespace App\Lib;


interface VideoDownloaderInterface
{
    /**
     * Обработчик прогресса загрузки файла
     *
     * @param \Closure $handler
     * @return void
     */
    public function onProgress(\Closure $handler);

    /**
     * Скачивает и сохраняет файл по пути $path. Возвращает true если операция удалась.
     *
     * @param string $path
     * @return void
     */
    public function save(string $path);
}