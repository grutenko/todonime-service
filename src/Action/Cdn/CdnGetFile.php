<?php


namespace App\Action\Cdn;


use App\Action\Action;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use League\Flysystem\Filesystem;
use function GuzzleHttp\Psr7\stream_for;

/** @property $cdn Filesystem */
class CdnGetFile extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $url = $args['path'];

        if( strlen(trim($args['path'], '/')) == 0 || !$this->cdn->has($url) ) {
            return $response->withStatus(404);
        }

        $stream = stream_for( $this->cdn->readStream($url) );

        return $response
            ->withHeader('Content-Type', 'application/otcet-stream')
            ->withBody($stream);
    }
}