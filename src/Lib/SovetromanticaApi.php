<?php


namespace App\Lib;


class SovetromanticaApi
{
    const BASE_URL = 'https://service.sovetromantica.com/v1/';

    /**
     * @param string $url
     * @param array $params
     * @return array|null
     */
    public function fetch(string $url, array $params = []): ?array
    {

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => static::BASE_URL . trim($url, '/') . '?' . http_build_query($params),
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        $rawData = curl_exec($ch);
        $data = json_decode( $rawData, true );

        echo $url. PHP_EOL;

        if(curl_errno($ch) != 0)
        {
            throw new \RuntimeException('CURL: '. curl_error($ch) );
        }

        if( json_last_error() != 0 )
        {
            throw new \RuntimeException('JSON: '. json_last_error_msg());
        }

        return $data;
    }
}