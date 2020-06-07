<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use MongoDB\BSON\ObjectId;
use MongoDB\InsertOneResult;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AddVideo extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        $params = $request->getParsedBody();
        $db = $this->mongodb->todonime;

        if($user == null)
        {
            return ResponseHelper::error($response, 'OPERATION_NOT_PERMITTED', [], '403');
        }

        $anime = $db->animes->findOne([
            '_id' => new ObjectId($params['anime_id'])
        ]);

        if($anime == null)
        {
            return ResponseHelper::error($response, 'UNKNOWN_ERROR');
        }

        /**
         * @var InsertOneResult $result
         */
        $result = $this->mongodb->todonime->videos->insertOne([
            'url'       => $params['url'],
            'anime_id'  => (int)$anime['shikimori_id'],
            'episode'   => (int)$params['episode'],
            'kind'      => $params['kind'],
            'lang'      => $params['lang'],
            'author'    => $params['author'],
            'domain'    => parse_url($params['url'], PHP_URL_HOST),
            'project'   => $this->getProject($params['author']),
            'uploader'  => $user['_id']
        ]);

        return ResponseHelper::success($response, [
            'video_id' => $result->getInsertedId()
        ]);
    }

    /**
     * @param string $author
     * @return ObjectId|null
     */
    public function getProject(string $author): ?ObjectId
    {
        $projects = $this
            ->mongodb
            ->todonime
            ->projects
            ->find()
            ->toArray();

        foreach($projects as $project)
        {
            if(preg_match($project['rgx'], $author))
            {
                return $project['_id'];
            }
        }
        return null;
    }
}