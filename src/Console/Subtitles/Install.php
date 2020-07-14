<?php


namespace App\Console\Subtitles;


use App\Console\TodonimeCommand;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Install extends TodonimeCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'subtitles:install';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Добавляет субтитры в базу данных по паттерну.')
            ->addUsage('subtitles:batch "/([0-9]+)/" --path="~/episodes"')
            ->addArgument(
                'pattern',
                InputArgument::REQUIRED,
                'RegExp паттерн где первой группой должен найтись номер серии.'
            )
            ->addOption(
                'anime-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Id аниме.'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Куки с валидной авторизацией для получения закрытых субтитров.'
            )
            ->addOption(
                'author',
                null,
                InputOption::VALUE_OPTIONAL,
                'Имя автора субтитров.'
            )
            ->addOption(
                'lang',
                null,
                InputOption::VALUE_OPTIONAL,
                'Язык субтитров.'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pattern = $input->getArgument('pattern');
        $animeId = (int)$input->getOption('anime-id');
        $path = $input->getOption('path');
        $author = $input->getOption('author');
        $lang = $input->getOption('lang') ?? 'ru';
        $collection = $this->container->get('mongodb')->todonime->subtitles;

        if(!file_exists($path))
        {
            throw new \RuntimeException('Path not found. '. $path);
        }

        foreach($this->getSubs($path, $pattern) as $episode => $sub)
        {
            if($collection->findOne([ 'anime_id' => $animeId, 'episode' => $episode ]) == null)
            {
                $collection->insertOne([
                    'anime_id'  => $animeId,
                    'episode'   => $episode,
                    'author'    => $author,
                    'language'  => $lang,
                    'data'      => $sub
                ]);
                $output->writeln("$episode ...");
            }
        }

        return 0;
    }

    protected function getSubs($path, $pattern)
    {
        $paths = array_filter(scandir($path), function($subFile) use($path, $pattern) {
            $matches = [];
            return is_file("$path/$subFile") && preg_match($pattern, $subFile, $matches) && $matches[1];
        });

        $subs = [];
        foreach($paths as $subFile)
        {
            $matches = [];
            preg_match($pattern, $subFile, $matches);
            $episode = (int)$matches[1];

            $subs[ $episode ] = file_get_contents("$path/$subFile");
        }

        return $subs;
    }
}