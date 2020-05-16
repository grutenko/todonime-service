<?php


namespace App\Action\Auth;


use App\Action\Action;
use App\Helper\AuthHelper;
use Grutenko\Shikimori\Sdk;
use MongoDB\Client;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use GuzzleHttp\Psr7;

/**
 * @property-read Sdk $shikimori_sdk
 * @property-read Client $mongodb
 */
class AuthFromShikimori extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        error_reporting(E_ALL);

        $params = $request->getQueryParams();
        if( !isset($params['code']) )  {
            return $response
                ->withStatus(400)
                ->withBody(Psr7\stream_for('Invalid request.'));
        }

        $tokenData = $this->shikimori_sdk->auth()
            ->getAccessToken($params['code'], 'https://todonime.ru/auth/complete');

        if(isset($tokenData['error'])) {
            return $response
                ->withStatus(400)
                ->withBody(Psr7\stream_for('Token error.'));
        }

        $this->shikimori_sdk
            ->useOauthToken($tokenData);

        $user = $this->shikimori_sdk
            ->user()
            ->current();

        $cUsers = $this->mongodb->todonime->users;

        if( null == $cUsers->findOne(['shikimori_id' => $user['id'] ]) ) {
            $cUsers->insertOne([
                'shikimori_id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'sex' => $user['sex'],
                'token' => $tokenData
            ]);
        } else
        {
            $cUsers->updateOne(
                [
                    'shikimori_id' => $user['id']
                ],
                ['$set' => [
                    'token' => $tokenData
                ]]
            );
        }

        $dbUser = $cUsers->findOne(['shikimori_id' => $user['id']]);

        $authHelper = new AuthHelper($this->mongodb);
        $code = $authHelper->genAuthCode($dbUser['_id']);

        $cookie = $request->getCookieParams();

        return $response
            ->withStatus(302)
            ->withHeader('Location', @$cookie['auth_back_url'] ?: '/')
            ->withHeader('Set-Cookie', "auth={$code}; HttpOnly; Path=/; Max-Age=31536000");
    }
}