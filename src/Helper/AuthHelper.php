<?php


namespace App\Helper;

use MongoDB\BSON\ObjectId;
use MongoDB\Client;


class AuthHelper
{
    /**
     * @var Client
     */
    private $db;

    /**
     * AuthHelper constructor.
     * @param Client $mongoDb
     */
    public function __construct(Client $mongoDb)
    {
        $this->db = $mongoDb;
    }

    /**
     * 
     *
     * @param ObjectId $id
     * @param string $token
     * @return void
     */
    public function logout(ObjectId $id, string $token)
    {
        $this->db->todonime->users->updateOne(
            [
                '_id' => $id
            ],
            [
                '$pull' => [ 'auth_code' => [ '$in' => [$token] ] ]
            ]
        );
    }

    /**
     * @param ObjectId $oid
     * @return string
     */
    public function genAuthCode(ObjectId $oid): string
    {
        $code = bin2hex(openssl_random_pseudo_bytes(16));

        $this->db->todonime->users->updateOne(
            [
                '_id' => $oid
            ],
            [
                '$addToSet' => [
                    'auth_code' => $code
                ]
            ]);

        return $code;
    }

    /**
     * @param string $code
     * @return array|null
     */
    public function getByCode(string $code): ?array
    {
        return $this->db->todonime->users->findOne(['auth_code' => $code]);
    }
}