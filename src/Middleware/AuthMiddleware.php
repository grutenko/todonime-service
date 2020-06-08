<?php


namespace App\Middleware;


use App\Helper\AuthHelper;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\BSON\UTCDateTime;
use Slim\Psr7\Request;

class AuthMiddleware
{
    /**
     * @var mixed
     */
    private $db;

    /**
     * AuthMiddleware constructor.
     * @param Container $container
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Container $container)
    {
        $this->db = $container->get('mongodb');
    }

    public function __invoke(Request $request, $handler)
    {
        $cookie = $request->getCookieParams();
        if( isset($cookie['auth']) && strlen($cookie['auth']) > 0) {
            $authHelper = new AuthHelper($this->db);
            $user = $authHelper->getByCode($cookie['auth']);

            if($user != null) {
                $request = $request
                    ->withAttribute('user', $user)
                    ->withAttribute('token', $cookie['auth']);

                $this->db->todonime->users->updateOne(
                    [
                        '_id' => $user['_id']
                    ],
                    ['$set' => [
                        'last_active' => new UTCDateTime()
                    ]]
                );
            }
        }

        return $handler->handle($request);
    }
}