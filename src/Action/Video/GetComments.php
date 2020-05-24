<?php


namespace App\Action\Video;


use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GetComments extends \App\Action\Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $user = $request->getAttribute('user');
        unset($user['token'], $user['auth_code']);

        $collection = $this->mongodb->todonime->comments;

        $comments = $collection->aggregate([
            ['$match' => [
                'anime_id'  => new ObjectId($params['anime_id']),
                'episode'   => (int)$params['episode']
            ]],
            ['$sort' => [
                'created_at' => -1
            ]],
            ['$lookup' => [
                'from' => 'users',
                'localField' => 'user_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]],
            ['$unset' => 'user_id'],
            ['$unwind' => '$user'],
            ['$addFields' => [
                'user' => '$user',
            ]]
        ])->toArray();

        foreach($comments as &$comment)
        {
            unset($comment['user']['token'], $comment['user']['auth_code']);
        }

        $result = [
            'comments'  => $comments,
            'user'      => $user
        ];
        return ResponseHelper::success($response, $result);
    }
}