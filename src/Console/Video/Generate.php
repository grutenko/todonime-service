<?php


namespace App\Console\Video;


use App\Lib\PartnerVideo\Getter;
use App\Lib\PartnerVideo\Getters\SmotretAnimeGetter;
use App\Lib\PartnerVideo\Getters\SovetromanticaGetter;
use App\Lib\PartnerVideo\ScreenshotGenerator;
use App\Lib\SmotretAnimeApi;
use App\Lib\SovetromanticaApi;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
use MongoDB\Database;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class Generate extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'video:generate';

    /**
     * @var Database
     */
    protected $db;

    /**
     * GetVideos constructor.
     * @param Container $container
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Container $container)
    {
        $this->db = $container->get('mongodb')->todonime;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Добавляет видео в базу вместе с субтитрами.')
            ->addOption(
                'binary',
                null,
                InputOption::VALUE_REQUIRED,
                'Путь до видео.'
            )
            ->addOption(
                'subtitles',
                null,
                InputOption::VALUE_REQUIRED,
                'Путь до субтитров'
            )
            ->addOption(
                'anime-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Shikimori ID аниме'
            )
            ->addOption(
                'episode',
                null,
                InputOption::VALUE_REQUIRED,
                'Номер эпизода'
            )
            ->addOption(
                'author',
                null,
                InputOption::VALUE_REQUIRED,
                'Название автора'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_OPTIONAL,
                'Перезаписывать все файлы.',
                'n'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list(
            'binary'    => $videoPath,
            'subtitles' => $subPath,
            'anime-id'  => $animeId,
            'episode'   => $episode,
            'author'    => $author,
            'force'     => $force
        ) = $input->getOptions();

        $force = $force == 'y';
        $animeId = (int)$animeId;
        $episode = (int)$episode;

        if( !file_exists($videoPath) )
        {
            throw new InvalidArgumentException("$videoPath не найден.");
        }
        if( !file_exists($subPath) )
        {
            throw new InvalidArgumentException("$subPath не найден.");
        }

        $newPath = $this->saveVideo($videoPath, $animeId, $episode);
        $this->createThumbnails($newPath, $animeId, $episode);

        $this->getApplication() 
             ->find('binary:install')
             ->run(new ArrayInput([]), $output);

        if($this->db->subtitles->findOne([ 'anime_id' => $animeId, 'episode' => $episode]) == null)
        {
            $this->db->subtitles->insertOne([
                'anime_id'  => $animeId,
                'episode'   => $episode,
                'data'      => file_get_contents($subPath)
            ]);
        }
        else
        {
            $this->db->subtitles->updateOne(
                ['anime_id' => $animeId, 'episode' => $episode],
                ['$set' => ['data' => file_get_contents($subPath)]]
            );
        }

        $binaryId = $this->db->binaries->findOne(['anime_id' => $animeId, 'episode' => $episode])['_id']->__toString();
        $subId = $this->db->subtitles->findOne(['anime_id' => $animeId, 'episode' => $episode])['_id']->__toString();

        if($this->db->videos->findOne(['anime_id' => $animeId, 'episode' => $episode, 'domain' => 'embed.todonime.ru']) == null)
        {
            $this->db->videos->insertOne([
                'url'       => "https://embed.todonime.ru/{$binaryId}_{$subId}",
                'anime_id'  => $animeId,
                'episode'   => $episode,
                'kind'      => 'sub',
                'language'  => 'ru',
                'author'    => $author,
                'domain'    => 'embed.todonime.ru',
                'uploader'  => new ObjectId('5edd18affa864db5b2a03ffa')
            ]);
        }
        else
        {
            $this->db->videos->updateOne(
                ['anime_id' => $animeId, 'episode' => $episode, 'domain' => 'embed.todonime.ru'],
                ['$set' => ['url' => "https://embed.todonime.ru/{$binaryId}_{$subId}"]]
            );
        }

        return 0;
    }

    private function createThumbnails($videoPath, $animeId, $episode)
    {
        $dst = ($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') 
            . "/thumbnails/$animeId/$episode";

        if(!file_exists(($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') . "/thumbnails/$animeId"))
        {
            mkdir(($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') . "/thumbnails/$animeId");
        }
        if(!file_exists($dst))
        {
            mkdir($dst);
        }

        $gen = new ScreenshotGenerator($videoPath);
        $gen->generate($dst);
    }

    private function saveVideo($path, $animeId, $episode)
    {
        $newPath = ($_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public') 
            . "/episodes/$animeId/$episode.mp4";

        $arPath = explode('.', $path);
        if( $arPath[ count($arPath) - 1 ] != 'mp4' )
        {
            echo `ffmpeg -i {$path} -c copy {$newPath}`;
        }

        copy($path, $newPath);
        return $newPath;
    }
}