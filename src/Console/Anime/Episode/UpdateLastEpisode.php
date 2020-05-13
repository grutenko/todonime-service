<?php


namespace App\Console\Anime\Episode;


use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateLastEpisode extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:episode.last.update';

    /**
     * @var Database
     */
    private $db;

    /**
     * GetAnimes constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
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
            ->setDescription('Обновляет последний существующий эпизод аниме.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $animes = $this->db->animes
            ->find([], ['name_ru' => 1, 'shikimori_id' => 1, 'last_episode' => 1])
            ->toArray();

        foreach($animes as $anime) {
            $output->write($anime['name_en']);
            $videos = $this->db->videos->aggregate([
               ['$match'=> [
                   'anime_id' => $anime['shikimori_id']
               ]],
                ['$sort' => [
                    'episode' => -1
                ]],
                ['$limit' => 1]
            ])->toArray();

            $this->db->animes->updateOne(
                [
                    'shikimori_id' => $anime['shikimori_id']
                ],
                ['$set' => [
                    'last_episode' => count($videos) > 0 ? $videos['0']['episode'] : 0
                ]]
            );

            $output->writeln('<info> done.</info>');
        }

        return 0;
    }
}