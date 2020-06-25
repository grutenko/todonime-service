<?php


namespace App\Action\Embed;


use MongoDB\BSON\ObjectId;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Player extends \App\Action\Action
{
    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $arIds = explode('_', $args['oid'], 2);

        $video = $this->mongodb->todonime->binaries->findOne([
            '_id' => new ObjectId($arIds[0])
        ]);

        if(!$video)
        {
            $response->getBody()->write( $this->twig->render('embed/not-found.twig') );
            return $response;
        }

        if( count($arIds) > 1 )
        {
            $sub = $this->mongodb->todonime->subtitles->findOne([
                '_id' => new ObjectId($arIds[1])
            ]);
            $video['sub'] = $sub;
        }

        $response->getBody()->write( $this->twig->render('embed/player.twig', $video) );
        return $response;
    }
}