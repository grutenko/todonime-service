<?php


namespace App\Console\Anime;


use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Fix extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:fix';

    /**
     * @var Database
     */
    private $db;

    /**
     * @var Sdk
     */
    private $sdk;

    /**
     * GetAnimes constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('mongodb')->todonime;
        $this->sdk = $container->get('shikimori_sdk');
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Обновляет даннве об аниме из shikimori.one');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for($page = 1; ; $page++) {
            $animes = $this->sdk->anime()->list([
                'limit' => 50,
                'order' => 'id',
                'page' => $page
            ]);

            if($animes->count() == 0) {
                break;
            }

            foreach($animes as $anime) {
                $output->write("\r"
                    . str_pad(
                        substr($anime->id . ' ' . $anime->name, 0, 70),
                        70
                    )
                );

                $this->db->animes->updateOne([
                    'shikimori_id' => $anime->id
                ], [
                    '$set' => ['rating' => (float)$anime->score]
                ]);
            }

            usleep(400000);
        }

        return 0;
    }
}