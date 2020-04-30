<?php


namespace App\Lib;


use RuntimeException;

class SmotretAnimeApi
{
    const BASE_URL = 'https://smotret-anime.online/api';

    /**
     * SmotretAnimeApi constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $path
     * @param array $params
     * @return array
     */
    public function send(string $path, $params = []): array
    {
        $url = static::BASE_URL . $path;
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Todonime'
            ]
        ]);

        $data = curl_exec($ch);

        if (curl_errno($ch) != 0) {
            throw new RuntimeException('CURL: ' . curl_error($ch));
        }
        $arData = json_decode($data, true);
        if (json_last_error() != null) {
            throw new RuntimeException('JSON: ' . json_last_error_msg());
        }

        return $arData;
    }
}