<?php


namespace App\Ws\Channel;


class CommentsChannel implements ChannelInterface
{

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'comments';
    }

    /**
     * @inheritDoc
     */
    public function chooseSubscribers(array $subscribers, array $data): array
    {
        $eventData = $data['eventData'];

        return array_filter($subscribers, function( $subscriber ) use($eventData) {
            return (
                !isset($subscriber->filter['anime_id'])
                || $subscriber->filter['anime_id'] == $eventData['anime_id']['$oid']
                ) &&
                (
                    !isset($subscriber->filter['episode'])
                    || $subscriber->filter['episode'] == $eventData['episode']
                );
        });
    }
}