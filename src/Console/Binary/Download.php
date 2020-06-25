<?php


namespace App\Console\Binary;


use App\Console\TodonimeCommand;
use App\Lib\PartnerVideo\Downloaders\SmotretAnimeDownloader;
use App\Lib\PartnerVideo\ScreenshotGenerator;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends TodonimeCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'binary:download';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Скачивает и устанавливает видеозапись используя данные iframe видео.')
            ->addUsage('video:download 5ea8cf118ae77bfc83061675')
            ->addUsage('video:download 5ea8cf118ae77bfc83061675 --cookie="auth=46j4532h42h356h78i656y24t13re3234t5y"')
            ->addUsage('video:download https://smotret-anime.online/translations/embed/3240812 --anime-id=32935 --episode=8')
            ->addArgument(
                'id',
                InputArgument::OPTIONAL,
                'ObjectID видео которое нужно скачать.'
            )
            ->addOption(
                'anime-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Shikimori Id аниме для этого видео.'
            )
            ->addOption(
                'episode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Номер эпизода для этого видео'
            )
            ->addOption(
                'cookie',
                null,
                InputOption::VALUE_OPTIONAL,
                'Файл с данными куки, необходим для проведения авторизации на smotret-anime.online',
                null
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_OPTIONAL,
                'Перезаписывает файл, если он уже сущесвует.',
                'n'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $videoId = $input->getArgument('id');
        $cookie = $input->getOption('cookie');
        $force = $input->getOption('force') == 'y';
        $db = $this->container->get('mongodb')->todonime;

        if(strlen($videoId) === 24 && strspn($videoId, '0123456789ABCDEFabcdef') === 24)
        {
            $video = $db->videos->findOne([ '_id' => new ObjectId($videoId) ]);

            list(
                'domain'            => $domain,
                'anime_id'          => $animeId,
                'episode'           => $episode,
                'partner_video_id'  => $partnerVideoId
                ) = $video;

            $useUrl = false;
        }
        else
        {
            if( !$input->hasOption('anime-id') || !$input->hasOption('episode') )
            {
                throw new \InvalidArgumentException('--anime-id и --episode обязательыне параметры при использовании URL');
            }

            $domain = parse_url($videoId, PHP_URL_HOST);
            $useUrl = true;
        }

        if($domain != 'smotret-anime.online')
        {
            throw new \InvalidArgumentException('Скачивание доступно только для видео с smotret-anime.online.');
        }
        if( !$cookie )
        {
            throw new \InvalidArgumentException('Для скачивания необходимо указать cookie с валидной авторизацией.');
        }

        if($useUrl)
        {
            $animeId = $input->getOption('anime-id');
            $episode = $input->getOption('episode');

            $matches = [];
            if(!preg_match('/translations\/embed\/([0-9]+)/', $videoId, $matches))
            {
                throw new \RuntimeException('Regex Error.');
            }

            $partnerVideoId = $matches[1];
        }

        $storage = $_ENV['PUBLIC_STORAGE_DIR'] ?: realpath(__API_DIR__ . '/storage/public');

        if(!file_exists("{$storage}/episodes/$animeId"))
        {
            mkdir("{$storage}/episodes/$animeId");
        }

        $src = "{$storage}/episodes/$animeId/$episode.mp4";

        if( file_exists($src) && !$force )
        {
            $output->writeln('<info>skipped.</info>');
            return 0;
        }

        $download = new SmotretAnimeDownloader(
            $partnerVideoId,
            $cookie,
            $force
        );
        $download->onProgress(function($downloaded, $total) use ($output) {
            $output->write("\r{$downloaded}/{$total} (". ($total ? floor($downloaded / $total * 100) : 0) ."%)");
        });

        $output->writeln('');
        $download->save( $src );
        $output->writeln('');

        if(!file_exists("{$storage}/thumbnails/$animeId"))
        {
            mkdir("{$storage}/thumbnails/$animeId");
        }
        if(!file_exists("{$storage}/thumbnails/$animeId/$episode"))
        {
            mkdir("{$storage}/thumbnails/$animeId/$episode");
        }
        $dst = "{$storage}/thumbnails/$animeId/$episode";

        $screenGen = new ScreenshotGenerator($src);
        $paths = $screenGen->generate($dst);

        $binary = $db->binaries->findOne([
            'anime_id' => (int)$animeId,
            'episode' => (int)$episode
        ]);

        $params = [
            'video'         => str_replace(rtrim($_ENV['PUBLIC_STORAGE_DIR'] ?: '/var/www/todonime.ru/current/storage/public'), '', $src),
            'anime_id'      => (int)$animeId,
            'episode'       => (int)$episode,
            'preview'       => str_replace(rtrim($_ENV['PUBLIC_STORAGE_DIR'] ?: '/var/www/todonime.ru/current/storage/public'), '', $paths['preview']),
            'screenshots'   => array_map(function($item) {
                $item['path'] = str_replace(rtrim($_ENV['PUBLIC_STORAGE_DIR'] ?: '/var/www/todonime.ru/current/storage/public'), '', $item['path']);
                return $item;}, $paths['screenshots'])
        ];

        if($binary)
        {
            $db->binaries->updateOne(['_id' => $binary['_id']], ['$set' => $params]);
        }
        else
        {
            $db->binaries->insertOne($params);
        }

        return 0;
    }
}