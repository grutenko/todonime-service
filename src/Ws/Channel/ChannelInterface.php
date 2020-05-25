<?php


namespace App\Ws\Channel;


use App\Ws\Subscriber;

interface ChannelInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Исходя из назвачения канала выбирает из подписчиков те, которым нужно отправить данные $data.
     * Данные должны хранить всю необходимую информацию для фильтрации, либо они ну будут распределены.
     *
     * @param array<Subscriber> $subscribers
     * @param array $data
     * @return array
     */
    public function chooseSubscribers(array $subscribers, array $data): array;
}