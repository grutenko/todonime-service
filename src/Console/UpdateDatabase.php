<?php


namespace App\Console;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\Database;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDatabase extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'db:update';

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
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Запускает все необходимые коносольные команды для обновления базы.')
            ->addOption(
                'skip',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Пропускает стадии'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skip = $input->getOption('skip');

        $commands = [
            ['video:get'],
            ['video:project.find'],
            ['anime:get'],
            ['anime:episode.last.update'],
            ['anime:status.update', ['--for' => 'ongoing,anons']],
            ['video:fully-translated.update']
        ];

        $commands = array_filter($commands, function($command) use($skip) {
            return !in_array($command[0], $skip);
        });

        foreach($commands as $command)
        {
            $output->writeln("<info>$command[0]</info>");

            $code = $this
                ->getApplication()
                ->find($command[0])
                ->run(new ArrayInput(@$command[1] ?: []), $output);

            if($code != 0 || $code != null) {
                return $code;
            }
        }

        return 0;
    }
}