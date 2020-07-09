<?php
namespace App\Action\User;

use App\Action\Action;
use App\Helper\AuthHelper;
use App\Helper\ResponseHelper;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use MongoDB\BSON\ObjectId;

class Watchlist extends Action
{
    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $query = $request->getQueryParams();
        $limit = $query['limit'] ?: 10;
        $offset = $limit * (($query['page'] ?: 1) - 1);

        if($user == null)
        {
            return ResponseHelper::error($response, 'USER_NOT_AUTH', [], 403);
        }

        $watched = $user['watched_episodes'];
        usort($watched, function($i1, $i2) {
            return (int)(isset($i2['updated_at']) ? $i2['updated_at']->__toString() : 0) - 
                (int)(isset($i1['updated_at']) ? $i1['updated_at']->__toString() : 0);
        });
        $watched = array_filter($watched, function($item) {
            return $item['episodes'] > 0;
        });
        $anime_ids = array_column($watched, 'anime_id');

        $animes = $this->mongodb->todonime->animes
            ->find([ '_id' => ['$in' => $anime_ids]])
            ->toArray();

        $result = [];
        foreach($watched as $item)
        {
            foreach($animes as $anime)
            {
                if($item['anime_id'] == $anime['_id']->__toString()
                    && $anime['last_episode'] > $item['episodes']
                    && isset($item['updated_at'])
                )
                {
                    $anime['watched'] = $item['episodes'];
                    $anime['in_watch_list'] = $item;
                    $result[] = $anime;
                }
            }
        }

        $result = array_slice($result, $offset, $limit);

        return ResponseHelper::success($response, array_values($result));
    }
}

