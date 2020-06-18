<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Class GetVideoByIdAction
 * @package App\Action\Video
 */
class GetVideoByIdAction extends Action
{
    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $video = $this->mongodb->todonime->videos->aggregate([
            [
                '$match' => ['_id' => new ObjectId($args['id'])]
            ],
            [
                '$limit' => 1
            ],
            [
                '$lookup' => [
                    'from' => 'animes',
                    'localField' => 'anime_id',
                    'foreignField' => 'shikimori_id',
                    'as' => 'anime'
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'videos',
                    'let' => [
                        'anime_id' => '$anime_id',
                        'episode' => '$episode'
                    ],
                    'pipeline' => [
                        [
                            '$match'=> [
                                '$expr' => [
                                    '$and' => [
                                        ['$eq' => ['$anime_id', '$$anime_id' ]],
                                        ['$eq' => ['$episode', '$$episode' ]]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'as' => 'videos'
                ]
            ],
            ['$unset' => 'anime_id'],
            ['$unwind' => '$anime'],
            ['$addFields' => [
                'anime' => '$anime',
            ]]
        ])->toArray();

        if (count($video) == 0) {
            return ResponseHelper::notFound($response, 'Video ' . $args['id'] . ' not found.');
        }

        $responseVideo = $video[0];
        $responseVideo['next_episode'] = $this->suggestNextEpisode($responseVideo);
        $responseVideo['prev_episode'] = $this->suggestPrevVideo($responseVideo);

        $user = $request->getAttribute('user');
        if($user != null) {
            unset($user['token'], $user['auth_code']);
            $responseVideo['user'] = $user;

            $responseVideo['is_watched'] = $this->episodeWatched(
                $user,
                $responseVideo['anime']['_id'],
                $responseVideo['episode']
            );
            $responseVideo['last_watched_episode'] = $this->getLastWatchedEpisode(
                $user,
                $responseVideo['anime']['_id']
            );
        }
        else
        {
            $responseVideo['user'] = null;
            $responseVideo['is_watched'] = false;
            $responseVideo['last_watched_episode'] = 0;
        }

        usort($responseVideo['videos'], function($v1, $v2) {
            $cmp = [
                'ru' => 3,
                'russian' => 3,
                'en' => 2,
                'english' => 2,
                'ja' => 1,
                'japan' => 1,
                'original' => 1
            ];

            return (($cmp[$v2['language']] ?: 3) + (int)@$v2['completed'])
                - (($cmp[$v1['language']] ?: 3) + (int)@$v1['completed']);
        });

        if( isset($responseVideo['project_id']) ) {
            $responseVideo['project'] = $this->mongodb->todonime->projects->findOne(
                ['_id' => $responseVideo['project_id']],
                [
                    'completed' => 0
                ]
            );
            unset($responseVideo['project_id']);
        }

        if( isset($responseVideo['uploader']) )
        {
            $uploader = $this->mongodb->todonime->users->findOne(
                ['_id' => $responseVideo['uploader']]
            );
            unset(
                $uploader['watched_episodes'],
                $uploader['auth_code'],
                $uploader['token']
            );
            $responseVideo['uploader'] = $uploader;
        }

        if(isset($responseVideo['anime']['episodes'][ $responseVideo['episode'] ]))
        {
            $responseVideo['name'] = $responseVideo['anime']['episodes'][ $responseVideo['episode'] ]['name'];
        }
        else
        {
            $responseVideo['name'] = 'Эпизод без имени';
        }

        return ResponseHelper::success($response, $responseVideo);
    }

    /**
     * @param array $user
     * @param ObjectId $animeId
     * @param int|string $episode
     * @return bool
     */
    private function episodeWatched( array $user, Objectid $animeId, $episode ): bool
    {
        return $this->getLastWatchedEpisode($user, $animeId) >= $episode;
    }

    /**
     * @param array $user
     * @param ObjectId $animeId
     * @return int
     */
    private function getLastWatchedEpisode(array $user, Objectid $animeId): int
    {
        if( !isset($user['watched_episodes']) )
        {
            return 0;
        }
        foreach($user['watched_episodes'] as $watched)
        {
            if( $watched['anime_id']->__toString() == $animeId->__toString() )
            {
                return $watched['episodes'];
            }
        }

        return 0;
    }

    /**
     * @param array $video
     * @return array
     */
    private function suggestPrevVideo(array $video): ?array
    {
        if($video['episode'] == 1) {
            return null;
        }

        return $this->suggest($video, $video['episode'] - 1);
    }

    private function suggestNextEpisode(array $video): ?array
    {
        if($video['episode'] >= $video['anime']['last_episode']) {
            if( $video['anime']['status'] != 'released' && null != $video['anime']['next_episode_at']) {
                return ['next_episode_at' => $video['anime']['next_episode_at']->__toString()];
            } else
            {
                return null;
            }
        }

        return $this->suggest($video, $video['episode'] + 1);
    }

    /**
     * @param array $video
     * @param int   $episode
     * @return array
     */
    private function suggest(array $video, int $episode): ?array
    {
        if( isset($video['project_id']) ) {
            $suggestVideo = $this->mongodb->todonime->videos->findOne([
                'anime_id' => (int)$video['anime']['shikimori_id'],
                'episode' =>(int) $episode,
                'kind' => $video['kind'],
                'domain' => $video['domain'],
                'project_id' => $video['project_id']
            ]);

            if($suggestVideo != null) {
                return [
                    'video_id' => $suggestVideo['_id']->__toString()
                ];
            } else
            {
                $suggestVideo = $this->mongodb->todonime->videos->findOne([
                    'anime_id' => (int)$video['anime']['shikimori_id'],
                    'episode' =>(int) $episode,
                    'kind' => $video['kind'],
                    'project_id' => $video['project_id']
                ]);

                if($suggestVideo != null) {
                    return [
                        'video_id' => $suggestVideo['_id']->__toString()
                    ];
                }
            }
        }

        $videos = $this->mongodb->todonime->videos->find([
           'anime_id' => (int)$video['anime']['shikimori_id'],
           'kind' => $video['kind'],
           'episode' => (int)$episode
        ])->toArray();

        if( count($videos) > 0 ) {
            return [
                'video_id' => $this->minLevensteinId($video['author'], $videos)->__toString()
            ];
        }

        $video = $this->mongodb->todonime->videos->findOne([
            'anime_id' => (int)$video['anime']['shikimori_id'],
            'episode' => (int)$episode
        ]);

        if($video == null) {
            return null;
        } else {
            return [
                'video_id' => $video['_id']->__toString()
            ];
        }
    }

    /**
     * @param $authorName
     * @param $videos
     * @return mixed
     */
    private function minLevensteinId($authorName, $videos)
    {
        $currentVideo = null;
        $ln = null;

        foreach($videos as $video)
        {
            $currentLn = levenshtein(
                substr($authorName, 0, 64),
                substr($video['author'], 0, 64)
            );
            if($ln == null || $ln > $currentLn) {
                $currentVideo = $video;
                $ln = $currentLn;
            }

            if($ln == 0) {
                return $currentVideo['_id'];
            }
        }

        return $currentVideo['_id'];
    }
}