<?php


namespace App\Worker;


use App\Lib\Queue\WorkerInterface;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Grutenko\Shikimori\Sdk;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;

class BumpEpisodeWorker implements WorkerInterface
{
    /**
     * @var Client
     */
    private $db;

    /**
     * @var Sdk
     */
    private $sdk;

    /**
     * BumpEpisodeWorker constructor.
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
     * @inheritDoc
     */
    public function handle(array $job): bool
    {
        /*
            REQUIRE
            user_id, anime_id, episode
        */

        $user = $this->db->users->findOne(['_id' => new ObjectId($job['user_id'])]);
        if($user == null) {
            return false;
        }

        $this->sdk->useOauthToken($user['token']);

        $anime = $this->db->animes->findOne(['_id' => new ObjectId($job['anime_id'])]);
        if($anime == null) {
            return false;
        }

        $anime = $this->sdk->anime()->find($anime['shikimori_id']);
        if($anime == null) {
            return false;
        }

        echo "{$user['nickname']} {$anime->name} {$job['episode']}\r\n";

        if($anime->user_rate == null) {
            $rate = $this->sdk->user()->createRate([
                'user_id' => $user['shikimori_id'],
                'target_id' => $anime['shikimori_id'],
                'target_type' => 'Anime',
                'status' => 'watching',
                'episodes' => $job['episode']
            ]);
        } else
        {
            $rate = $this->sdk->user()
                ->bumpEpisode($anime->user_rate['id'], $job['episode']);
        }

        return $rate != null;
    }
}