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
        $id = $args['oid'];
        $sub = $this->mongodb->todonime->binaries->findOne([
            '_id' => new ObjectId($id)
        ]);

        if(!$sub)
        {
            $response->getBody()->write( $this->twig->render('embed/not-found.twig') );
            return $response;
        }

        $response->getBody()->write( $this->twig->render('embed/player.twig', $sub) );
        return $response;
    }
}