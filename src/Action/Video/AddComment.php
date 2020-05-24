<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AddComment extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody();
        $user = $request->getAttribute('user');

        /** @var Collection $collection */
        $collection = $this->mongodb->todonime->comments;

        $result = $collection->insertOne([
            'anime_id'      => new ObjectId($params['anime_id']),
            'episode'       => (int)$params['episode'],
            'created_at'    => new UTCDateTime(time() * 1000),
            'user_id'       => $user['_id'],
            'text'          => trim($params['text'])
        ]);

        $id = $result->getInsertedId();

        $comment = $collection->aggregate([
            ['$match' => [
                '_id' => $id
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
        ])->toArray()[0];
        unset($comment['user']['token'], $comment['user']['auth_code']);

        return ResponseHelper::success($response, $comment);
    }
}