<?php


namespace App\Lib;


class KodikApi
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * KodikApi constructor.
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
}