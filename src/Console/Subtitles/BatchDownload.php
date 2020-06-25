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

class BatchDownload extends TodonimeCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'subtitles:batch';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Добавляет субтитры исходя из запроса query в формате json.')
            ->addUsage('subtitles:batch \'{anime_id: 21, kind: "dub", language: {"$in": ["jp", "japan"]}}\' --force=y')
            ->addArgument(
                'query',
                InputArgument::REQUIRED,
                'Mongo запрос в формате JSON. Для project_id можно указывать строку с id.'
            )
            ->addOption(
                'cookie',
                null,
                InputOption::VALUE_REQUIRED,
                'Куки с валидной авторизацией для получения закрытых субтитров.'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cookie = $input->getOption('cookie');
        $query = json_decode($input->getArgument('query'), true);

        if(json_last_error())
        {
            throw new \RuntimeException('JSON: '. json_last_error_msg());
        }

        if( isset($query['project_id']) )
        {
            $query['project_id'] = new ObjectId($query['project_id']);
        }
        $query['domain'] = 'smotret-anime.online';
        $query['kind'] = 'sub';

        $videos = $this->container->get('mongodb')->todonime->videos->find($query)->toArray();

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Найдено '. count($videos). '. Запустить скачиваниe?',
            false
        );

        if ( !$helper->ask($input, $output, $question) )
        {
            return 0;
        }

        foreach($videos as $video)
        {
            try {
                $this
                    ->getApplication()
                    ->find('subtitles:download')
                    ->run(
                        new ArrayInput([ 'id' => $video['_id']->__toString(), '--cookie' => $cookie, '--ignore-errors' => 'y' ]),
                        $output
                    );
            }
            catch(Exception $e)
            {
                $output->writeln('<error>error.</error>');
            }
        }

        return 0;
    }
}