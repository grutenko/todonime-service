<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class DeleteComment extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $commentId = $args['commentId'];
        $user = $request->getAttribute('user');

        $comment = $this
            ->mongodb
            ->todonime
            ->comments
            ->findOne([
                '_id' => new ObjectId($commentId)
            ]);

        if(
            $comment['user_id']->__toString() != $user['_id']->__toString() &&
            !in_array('admin', $user['scope'] ?: [])
        )
        {
            return ResponseHelper::error($response, 'OPERATION_NOT_PERMITTED', [], '403');
        }

        $this
            ->mongodb
            ->todonime
            ->comments
            ->deleteOne([
                '_id' => new ObjectId($commentId)
            ]);

        $this->event_dispatcher->send("comments", "delete", [
            "anime_id"      => $comment['anime_id'],
            'episode'       => $comment['episode'],
            'comment_id'    => $commentId
        ]);

        return ResponseHelper::success($response, []);
    }
}