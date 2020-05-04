<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
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
                    'from' => 'episodes',
                    'let' => [
                        'anime_id' => '$anime_id',
                        'episode' => '$episode'
                    ],
                    'pipeline' => [
                        [
                            '$match'=> [
                                '$expr' => [
                                    '$and' => [
                                        ['$eq' => ['$_id.anime_id', '$$anime_id' ]],
                                        ['$eq' => ['$_id.episode', '$$episode' ]]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'as' => 'videos_ids'
                ]
            ],
            ['$unset' => 'anime_id'],
            ['$unwind' => '$anime'],
            ['$unwind' => '$videos_ids'],
            ['$addFields' => [
                'anime' => '$anime',
                'videos_ids' => '$videos_ids.videos'
            ]],
            [
                '$lookup' => [
                    'from' => 'videos',
                    'localField' => 'videos_ids',
                    'foreignField' => '_id',
                    'as' => 'videos'
                ]
            ],
            ['$unset' => 'videos_ids']
        ])->toArray();

        if (count($video) == 0) {
            return ResponseHelper::notFound($response, 'Video ' . $args['id'] . ' not found.');
        }

        $responseVideo = $video[0];
        $responseVideo['next_episode'] = $this->suggestNextEpisode($responseVideo);
        $responseVideo['prev_episode'] = $this->suggestPrevVideo($responseVideo);

        return ResponseHelper::success($response, $responseVideo);
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
            if($video['anime']['status'] == 'released') {
                return null;
            } else
            {
                return ['next_episode_at' => $anime['next_episode_at']];
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
        $videos = $this->mongodb->todonime->videos->find([
            'anime_id' => $video['anime']['shikimori_id'],
            'episode'  => $episode
        ])->toArray();

        if(count($videos) == 0) {
            return null;
        }

        $ls = -1;
        $similarAuthor = null;
        foreach($videos as $suggest) {
            if($similarAuthor == null) {
                $similarAuthor = $suggest;
                $ls = levenshtein($suggest['author'], $video['author']);
                continue;
            }

            $tmpLs = levenshtein($suggest['author'], $video['author']);
            if( $tmpLs < $ls ) {
                $tmpLs = $ls;
                $similarAuthor = $suggest;
            }
        }

        if($similarAuthor['kind'] == $video['kind'] && $similarAuthor['language'] == $video['language']) {
            return [
                'video_id' => $similarAuthor['_id']->__toString()
            ];
        }

        foreach($videos as $suggest) {
            if( isset($suggest['project']) ) {
                return [
                    'video_id' => $suggest['_id']->__toString()
                ];
            }
        }

        foreach($videos as $suggest) {
            if( $suggest['kind'] == $video['kind'] && $suggest['domain'] == $video['domain']) {
                return [
                    'video_id' => $suggest['_id']->__toString()
                ];
            }
        }

        foreach($videos as $suggest) {
            if( $suggest['kind'] == $video['kind']) {
                return [
                    'video_id' => $suggest['_id']->__toString()
                ];
            }
        }

        return $videos[0];
    }
}