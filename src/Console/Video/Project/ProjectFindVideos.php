<?php


namespace App\Console\Video\Project;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectFindVideos extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'video:project.find';

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
            ->setDescription('Ищет и привязывает к проекту видео по регулярному выражению автора.')
            ->addArgument(
                'projects',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                "Имена проектов для которых нужно провести поиск."
            )
            ->addOption(
                'delete-all',
                null,
                InputOption::VALUE_OPTIONAL,
                'Удаляет все текущие привязки к проектам.',
                'n'
            )
            ->addOption(
                'reset',
                null,
                InputOption::VALUE_OPTIONAL,
                'Перезапишет значение project_id если оно существует у видео.',
                'n'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if( is_array($input->getArgument('projects')) && count($input->getArgument('projects')) > 0 ) {
            $filter = [
                'name' => [
                    '$in' => $input->getArgument('projects')
                ]
            ];
        } else
        {
            $filter = [];
        }

        $projects = $this->db
            ->projects
            ->find($filter)
            ->toArray();

        $count = 0;

        if( $input->getOption('delete-all') != 'n' ) {
            $output->write('Удаляю все привязки для '. implode(', ', array_column($projects, 'name')) .'...');
            $this->db->videos->updateMany(
                [
                    'project_id' => [
                        '$in' => array_column($projects, '_id')
                    ]
                ],
                ['$unset' => [
                    'project_id' => 1
                ]
                ]);
            $output->writeln('<info> done.</info>');
        }

        foreach($projects as $project)
        {
            $output->write($project['name']);
            $filter = [
                'author' => $project['rgx']
            ];

            if($input->getOption('reset') == 'n') {
                $filter['project_id'] = ['$exists' => false];
            }

            $res = $this->db->videos->updateMany(
                ['author' => $project['rgx']],
                ['$set' => [
                        'project_id' => $project['_id']
                    ]
                ]
            );

            $count += $res->getModifiedCount();
            $output->writeln('<info> done.</info>');
        }

        $output->writeln("\nAdded: {$count}. done.");
        return 0;
    }
}