<?php


namespace App\Action\Video;


use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class DeleteComment extends \App\Action\Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $commentId = $args['commentId'];
        $this
            ->mongodb
            ->todonime
            ->comments
            ->deleteOne([
                '_id' => new ObjectId($commentId)
            ]);
        return ResponseHelper::success($response, []);
    }
}