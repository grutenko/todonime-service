<?php


namespace App\Lib\PartnerVideo\Downloaders;


use App\Lib\VideoDownloaderInterface;

class SmotretAnimeDownloader implements VideoDownloaderInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string|null
     */
    public $cookie;

    /**
     * @var
     */
    public $force;

    /**
     * @var \Closure
     */
    public $onProgressHandler;

    /**
     * SmotretAnimeDownloader constructor.
     * @param int $videoId
     * @param string|null $cookie
     * @param bool $force
     */
    public function __construct(int $videoId, ?string $cookie = null, bool $force = false)
    {
        $this->id = $videoId;
        $this->cookie = $cookie;
        $this->force = $force;
    }

    /**
     * @inheritDoc
     */
    public function onProgress(\Closure $handler)
    {
        $this->onProgressHandler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function save(string $path)
    {
        if( !$this->force && file_exists($path) )
        {
            throw new \RuntimeException("File {$path} exists.");
        }
        if(false === ($out = fopen($path, 'wb')) )
        {
            throw new \RuntimeException('IO Error');
        }

        $ch = curl_init("https://smotret-anime.online/translations/mp4/{$this->id}?format=mp4");

        $params = [
            CURLOPT_HEADER              => 0,
            CURLOPT_FOLLOWLOCATION      => 1,
            CURLOPT_FILE                => $out,
            CURLOPT_PROGRESSFUNCTION    => function($resource,$download_size, $downloaded, $upload_size, $uploaded) {
                call_user_func($this->onProgressHandler, $downloaded, $download_size);
            },
            CURLOPT_NOPROGRESS          => 0
        ];

        if($this->cookie)
        {
            $params[CURLOPT_COOKIESESSION] = 1;
            $params[CURLOPT_COOKIE] = $this->cookie;
        }

        curl_setopt_array($ch, $params);
        curl_exec($ch);

        if(curl_errno($ch))
        {
            throw new \RuntimeException("CURL: ". curl_error($ch));
        }
        fclose($out);
    }
}