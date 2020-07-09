<?php


namespace App\Action\User;


use App\Action\Action;
use App\Helper\ResponseHelper;
use App\Lib\Queue\Client;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use MongoDB\BSON\UTCDateTime;
use function GuzzleHttp\Psr7\parse_query;

class BumpEpisode extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        if($request->getAttribute('user') == null) {
            return ResponseHelper::error($response, 'USER_NOT_AUTH', '', 403);
        }

        $user = $request->getAttribute('user');
        $params = parse_query($request->getBody()->getContents());

        if(!isset($params['anime_id']) || !isset($params['episode'])) {
            return ResponseHelper::error($response, 'PARAMS_ERROR', '', 400);
        }

        $queue = new Client($this->mongodb->todonime->queue);

        $queue->add('bump_episode', [
            'anime_id' => $params['anime_id'],
            'user_id' => $user['_id']->__toString(),
            'episode' => $params['episode']
        ]);


        if( isset($user['watched_episodes']) )
        {
            $animeId = new ObjectId($params['anime_id']);
            $indx = $this->getWatchedEpisodeIndx( $user, $animeId );

            if($indx == -1)
            {
                $this->mongodb->todonime->users->updateOne(
                    [
                        '_id' => $user['_id']
                    ],
                    ['$addToSet' => [
                        'watched_episodes' => [
                            'anime_id' => $animeId,
                            'updated_at' => new UTCDateTime,
                            'episodes' => (int)$params['episode']
                        ]
                    ]]
                );
            }
            else
            {
                $this->mongodb->todonime->users->updateOne(
                    [
                        '_id' => $user['_id']
                    ],
                    ['$set' => [
                        "watched_episodes.{$indx}.episodes" => (int)$params['episode'],
                        "watched_episodes.{$indx}.updated_at" => new UTCDateTime
                     ]]
                );
            }
        }

        return ResponseHelper::success($response);
    }

    /**
     * @param array $user
     * @param ObjectId $animeId
     * @return int
     */
    private function getWatchedEpisodeIndx(array $user, ObjectId $animeId): int
    {
        foreach($user['watched_episodes'] as $indx => $watched)
        {
            if($watched['anime_id']->__toString() == $animeId->__toString())
            {
                return $indx;
            }
        }

        return -1;
    }
}