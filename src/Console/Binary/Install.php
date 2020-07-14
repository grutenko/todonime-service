<?php


namespace App\Console\Binary;


use App\Console\TodonimeCommand;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends TodonimeCommand
{
    protected $baseDir;
    protected $collection;

    /**
     * @var string
     */
    protected static $defaultName = 'binary:install';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Обходит все файлы, скриншоты и превью и сверяет их наличие в базе при отсутствии добавляет записи в базу.')
            ->addUsage('binary:install');

        $this->binaries = ($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') . '/episodes';
        $this->thumbnails = ($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') . '/thumbnails';
        $this->collection = $this->container->get('mongodb')->todonime->binaries;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $added = 0;
        $failed = 0;

        foreach($this->getAnimeids() as $animeId)
        {
            $output->writeln("$animeId ...");
            foreach($this->getAnimeEpisodes($animeId) as $episodeFilename)
            {
                if(!$this->existInDb($animeId, explode('.', $episodeFilename)[0]))
                {
                    try
                    {
                        $this->create($animeId, $episodeFilename);
                        $added++;
                    }
                    catch(\Exception $e)
                    {
                        $failed++;
                        $output->writeln("<error>{$e->getMessage()}</error>");
                    }
                }
            }
        }

        $output->writeln("<info>$added добавлено. $failed ошибок.</info>");
        return 0;
    }

    private function create($animeId, $episodeFilename)
    {
        $episode = explode('.', $episodeFilename)[0];
        $screens = $this->getScreens($animeId, $episode);
        $screens = array_map(function($screen, $i)
        {
            return [
                "path" => $screen,
                "start" => ($i*20) + 1,
                "end" => ($i + 1)*20
            ];
        }, $screens, array_keys($screens));
        $preview = "/thumbnails/$animeId/$episode/preview.jpg";

        $this->collection->insertOne([
            'anime_id'      => (int)$animeId,
            'episode'       => (int)$episode,
            'video'         => "/episodes/$animeId/$episodeFilename",
            'preview'       => $preview,
            'screenshots' => $screens
        ]);
    }

    private function previewExists($animeId, $episode)
    {
        return file_exists("{$this->thumbnails}/$animeId/$episode/preview.png");
    }

    /**
     * Undocumented function
     *
     * @param [type] $animeId
     * @param [type] $episode
     * @return array
     */
    private function getScreens($animeId, $episode)
    {
        $path = "{$this->thumbnails}/$animeId/$episode";
        if(!file_exists($path) || !is_dir($path))
        {
            throw new \RuntimeException("Invalid path {$this->thumbnails}/$animeId/$episode");
        }

        $screens = array_values(array_filter(scandir($path), function($screen) use ($path) {
            return is_file("$path/$screen") && preg_match('/^screen-[0-9]+\.(jpg|png)$/', $screen);
        }));

        usort($screens, function($s1, $s2) {
            $matches1 = [];
            $matches2 = [];
            preg_match('/screen-([0-9]+)\./', $s1, $matches1);
            preg_match('/screen-([0-9]+)\./', $s2, $matches2);

            return (int)$matches1[1] - (int)$matches2[1];
        });

        return array_map(function($screen) use ($animeId, $episode) {
            return "/thumbnails/$animeId/$episode/$screen";
        }, $screens);
    }

    /**
     * Undocumented function
     *
     * @param [type] $animeId
     * @param [type] $episode
     * @return bool
     */
    private function existInDb($animeId, $episode)
    {
        return $this->collection->findOne(["anime_id" => (int)$animeId, "episode" => (int)$episode]) != null;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    private function getAnimeIds(): array
    {
        if( !file_exists($this->binaries) && !is_dir($this->binaries) )
        {
            throw new \RuntimeException("Invalid path {$this->binaries}");
        }

        return array_filter(scandir($this->binaries), function($animeId) {
            return preg_match('/^[0-9]+$/', $animeId) && is_dir("{$this->binaries}/$animeId");
        });
    }

    /**
     * Undocumented function
     *
     * @param [type] $animeId
     * @return array
     */
    private function getAnimeEpisodes($animeId)
    {
        if( !file_exists("{$this->binaries}/$animeId") && !is_dir("{$this->binaries}/$animeId") )
        {
            throw new \RuntimeException("Invalid path {$this->binaries}/$animeId");
        }

        return array_filter(scandir("{$this->binaries}/$animeId"), function($episode) use ($animeId) {
            return preg_match('/^[0-9]+\.(mp4|webm)$/', $episode) && is_file("{$this->binaries}/$animeId/$episode");
        });
    }
}