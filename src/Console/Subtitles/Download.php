<?php


namespace App\Console\Subtitles;


use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends \App\Console\TodonimeCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'subtitles:download';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Скачивает и устанавливает субтитры.')
            ->addUsage('subtitles:download 5ea8cf118ae77bfc83061675')
            ->addUsage('subtitles:download 5ea8cf118ae77bfc83061675 --cookie="auth=46j4532h42h356h78i656y24t13re3234t5y"')
            ->addUsage('subtitles:download https://smotret-anime.online/translations/embed/3240812 --anime-id=32935 --episode=8')
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
            )
            ->addOption(
                'ignore-errors',
                null,
                InputOption::VALUE_OPTIONAL,
                'Игнорировать ошибки.',
                'n'
            );
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $videoId = $input->getArgument('id');
        $cookie = $input->getOption('cookie');
        $force = $input->getOption('force') == 'y';
        $ignoreErrors = $input->getOption('ignore-errors') == 'y';
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
                if($ignoreErrors)
                {
                    $output->writeln('--anime-id и --episode обязательные параметры при использовании URL');
                    return 1;
                }
                throw new \InvalidArgumentException('--anime-id и --episode обязательные параметры при использовании URL');
            }

            $domain = parse_url($videoId, PHP_URL_HOST);
            $useUrl = true;
        }

        if($domain != 'smotret-anime.online')
        {
            if($ignoreErrors)
            {
                $output->writeln('Скачивание доступно только для видео с smotret-anime.online.');
                return 1;
            }
            throw new \InvalidArgumentException('Скачивание доступно только для видео с smotret-anime.online.');
        }
        if( !$cookie )
        {
            if($ignoreErrors)
            {
                $output->writeln('Для скачивания необходимо указать cookie с валидной авторизацией.');
                return 1;
            }
            throw new \InvalidArgumentException('Для скачивания необходимо указать cookie с валидной авторизацией.');
        }

        if($useUrl)
        {
            $animeId = $input->getOption('anime-id');
            $episode = $input->getOption('episode');

            $matches = [];
            if(!preg_match('/translations\/embed\/([0-9]+)/', $videoId, $matches))
            {
                if($ignoreErrors)
                {
                    $output->writeln('Regex Error.');
                    return 1;
                }
                throw new \RuntimeException('Regex Error.');
            }

            $partnerVideoId = $matches[1];
        }

        $ass = file_get_contents(
            "https://smotret-anime.online/translations/ass/{$partnerVideoId}?download=1",
            false,
            stream_context_create([
                'http' => [
                    'header' => [
                        "Cookie: {$cookie}"
                    ],
                    'ignore_errors' => true
                ]
            ])
        );

        $status_line = $http_response_header[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $status = $match[1];

        if($status != 200)
        {
            if($ignoreErrors)
            {
                $output->writeln('Ошибка получения субититров: '. $status);
                return 1;
            }
            throw new \RuntimeException('Ошибка получения субититров: '. $status);
        }

        $params = [
            'partner_video_id' => (int)$partnerVideoId,
            'data'      => $ass,
            'anime_id'  => $animeId,
            'episode'   => $episode
        ];
        if(!$useUrl)
        {
            $params['author'] = $video['author'];
            if( isset($video['project_id']) )
            {
                $params['project_id'] = $video['project_id'];
            }
            $params['language'] = $video['language'];
        }

        $this->container->get('mongodb')->todonime->subtitles->insertOne($params);

        $output->writeln('<info>done.</info>');

        return 0;
    }
}