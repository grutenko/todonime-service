<?php


namespace App\Lib\PartnerVideo\Getters;


use App\Lib\PartnerVideo\GetterInterface;
use App\Lib\SmotretAnimeApi;
use Generator;
use MongoDB\Database;

class SmotretAnimeGetter implements GetterInterface
{
    /**
     * @var SmotretAnimeApi
     */
    private $api;

    /**
     * SmotretAnimeGetter constructor.
     * @param SmotretAnimeApi $api
     */
    public function __construct(SmotretAnimeApi $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'smotret-anime.online';
    }

    /**
     * @inheritDoc
     */
    public function getLastId(): int
    {
        return $this->api->send('/translations', [
            'feed' => 'recent',
            'limit' => 1
        ])['data'][0]['id'];
    }

    /**
     * @inheritDoc
     */
    public function videoGenerator(Database $db): Generator
    {
        $lastVideoId = $this->getLastInsertedVideoId($db);

        while (true) {
            $translations = $this->api->send('/translations', [
                'feed' => 'id',
                'afterId' => $lastVideoId,
                'limit' => 5000
            ]);

            if (count($translations['data']) == 0) {
                break;
            }

            yield array_map(function ($video) {
                if($video['typeKind'] == 'voice') {
                    $kind = 'dub';
                } elseif($video['typeKind'] == 'subtitles')
                {
                    $kind = 'sub';
                } else
                {
                    $kind = 'org';
                }

                return [
                    'url' => $video['embedUrl'],
                    'anime_id' => $video['series']['myAnimeListId'],
                    'episode' => $video['episode']['episodeInt'],
                    'language' => $video['typeLang'],
                    'kind' => $kind,
                    'author' => $video['authorsSummary'],
                    'domain' => 'smotret-anime.online',
                    'partner_video_id' => $video['id']
                ];
            }, $translations['data']);

            $lastVideoId = array_pop($translations['data'])['id'];
        }
    }

    /**
     * @param Database $db
     * @return int
     */
    private function getLastInsertedVideoId(Database $db)
    {
        $lastVideo = $db->videos
            ->find([], ['sort' => ['partner_video_id' => -1], 'domain' => 'smotret-anime.online', 'limit' => 1])
            ->toArray();

        return count($lastVideo) > 0
            ? $lastVideo[0]['partner_video_id']
            : 0;
    }
}