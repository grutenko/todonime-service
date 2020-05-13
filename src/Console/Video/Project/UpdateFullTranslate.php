<?php


namespace App\Console\Video\Project;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFullTranslate extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'video:fully-translated.update';

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
        $this->setDescription('Обновляет кеш проектов с аниме, которые они перевели полностью.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->db->projects
            ->find()
            ->toArray();

        $output->write('Удаляю текущие данные о полностью перевенных аниме.');
        $this->db->videos->updateMany(
            ['completed' => true],
            [
                '$unset' => [
                    'completed' => 1
                ]
            ]
        );
        $output->writeln('<info> done.</info>');

        foreach($projects as $project)
        {
            $output->write($project['name']);

            $animes = [];
            $videos = $this->db->videos
                ->find([ 'project_id' => $project['_id'] ])
                ->toArray();

            uasort($videos, function($v1, $v2) {
                return $v2['episode'] - $v1['episode'];
            });

            $this->db->videos->updateMany(['completed' => true], [
               '$unset' => [
                   'completed' => 1
               ]
            ]);

            foreach($videos as $video)
            {
                $animes[ $video['anime_id'] ][] = $video['episode'];
            }

            $animes = array_filter($animes, function($episodes, $anime_id) use($output) {
               $anime = $this->db->animes->findOne([
                   'shikimori_id' => $anime_id
               ]);

               return is_array($anime)
                   && isset($anime['last_episode'])
                   && count( array_unique($episodes) ) >= $anime['last_episode'];
            }, ARRAY_FILTER_USE_BOTH);

            foreach(array_keys($animes) as $anime_id) {
                $this->db->videos->updateMany(
                    [
                        'anime_id' => $anime_id,
                        'project_id' => $project['_id']
                    ],
                    [
                        '$set' => [
                            'completed' => true
                        ]
                    ]
                );
            }

            $output->writeln('<info> done.</info>');
        }

        $projects = $this->db->projects
            ->find()
            ->toArray();

        return 0;
    }
}