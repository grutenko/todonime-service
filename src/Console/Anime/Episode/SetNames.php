<?php


namespace App\Console\Anime\Episode;


use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetNames extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:episode.names.set';

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
            ->setDescription('Устанавливает названия эпизодов из переданного в stdin JSON.')
            ->addArgument(
                'anime_id',
                InputArgument::REQUIRED,
                'Shikimori ID аниме'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $animeId = $input->getArgument('anime_id');
        $raw = file_get_contents('php://stdin');
        $json = json_decode($raw, true);

        if(!$json)
        {
            throw new \RuntimeException('JSON: '. json_last_error_msg() . ' '. $raw);
        }

        $this->db->animes->updateOne(
            [
                'shikimori_id' => (int)$animeId
            ],
            ['$set' => [
                'episodes' => $json
            ]]
        );
        $output->writeln('<info>done.</info>');

        return 0;
    }
}