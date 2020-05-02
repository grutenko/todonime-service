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

        return ResponseHelper::success($response, $video[0]);
    }
}