<?php


namespace App\Helper;


use GuzzleHttp\Psr7;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Message;
use Slim\Psr7\Response;

class ResponseHelper
{
    /**
     * @param Response $response
     * @param mixed $details
     * @return MessageInterface|ResponseInterface|Message|Response
     */
    public static function notFound(Response $response, $details = [])
    {
        return self::error($response, 'NOT_FOUND', $details, 404);
    }

    /**
     * @param Response $response
     * @param string $code
     * @param array|mixed $details
     * @param int $httpStatus
     * @return MessageInterface|ResponseInterface|Message|Response
     */
    public static function error(Response $response, string $code, $details = [], $httpStatus = 500)
    {
        $body = json_encode([
            'success' => false,
            'error' => $code,
            'error_description' => $details
        ]);
        return $response
            ->withStatus($httpStatus)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', 'https://todonime.ru')
            ->withBody(Psr7\stream_for($body));
    }

    /**
     * @param Response $response
     * @param array $data
     * @return MessageInterface|ResponseInterface|Message|Response
     */
    public static function success(Response $response, array $data = [])
    {
        $body = json_encode([
            'success' => true,
            'data' => $data
        ]);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', 'https://todonime.ru')
            ->withBody(Psr7\stream_for($body));
    }
}