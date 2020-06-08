<?php


namespace App\Lib\PartnerVideo\Getters;


use App\Lib\PartnerVideo\GetterInterface;
use App\Lib\SovetromanticaApi;
use Generator;
use MongoDB\Database;

class SovetromanticaGetter implements GetterInterface
{

    /**
     * @var SovetromanticaApi
     */
    public $api;

    /**
     * SovetromanticaGetter constructor.
     * @param SovetromanticaApi $api
     */
    public function __construct(SovetromanticaApi $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Sovetromantica';
    }

    /**
     * @inheritDoc
     */
    public function getLastId(): int
    {
        return -1;
    }

    /**
     * @inheritDoc
     */
    public function videoGenerator(Database $db): Generator
    {
        $lim = 30;

        $db->videos->deleteMany([
            'domain' => 'sovetromantica.com'
        ]);

        for($offset = 0; ; $offset += $lim )
        {
            $animes = $this->api->fetch('list', [
               'limit'  => $lim,
               'offset' => $offset
            ]);

            if( count($animes) == 0 )
            {
                break;
            }
            yield $this->processBatch($db, $animes);
        }
    }

    /**
     * @param Database $db
     * @param array $batch
     * @return array
     */
    private function processBatch(Database $db, array $batch)
    {
        $allEpisodes = [];
        foreach($batch as $anime) {
            $episodes = $this->api->fetch("anime/{$anime['anime_id']}/episodes");
            $data = $this->processEpisodes($db, $anime, $episodes);

            $allEpisodes = array_merge($allEpisodes, $data);
        }

        return $allEpisodes;
    }

    /**
     * @param array $anime
     * @param array $episodes
     * @return array[]
     */
    private function processEpisodes(Database $db, array $anime, array $episodes)
    {
        $project = $db->projects->findOne([
           'name' => 'SovetRomantica'
        ]);

        $episodes = array_filter($episodes, function($episode) {
            return isset($episode['embed'])
                && isset($episode['episode_count'])
                && isset($episode['episode_type'])
                && isset($episode['episode_id']);
        });

        return array_map(function($episode) use($anime, $project) {
            return [
                'url'               => $episode['embed'],
                'anime_id'          => (int)$anime['anime_shikimori'],
                'episode'           => (int)$episode['episode_count'],
                'language'          => 'ru',
                'kind'              => $episode['episode_type'] ? 'dub' : 'sub',
                'author'            => 'Sovetromantica',
                'domain'            => 'sovetromantica.com',
                'project_id'        => $project['_id'],
                'partner_video_id'  => (int)$episode['episode_id']
            ];
        }, $episodes);
    }
}