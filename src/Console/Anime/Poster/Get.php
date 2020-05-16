<?php


namespace App\Console\Anime\Poster;


use Grutenko\Shikimori\Sdk;
use League\Flysystem\Filesystem;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use RuntimeException;
use \Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Get extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:poster.get';

    /**
     * @var Database
     */
    private $db;

    /**
     * @var Sdk
     */
    private $sdk;

    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * GetAnimes constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('mongodb')->todonime;
        $this->sdk = $container->get('shikimori_sdk');
        $this->cdn = $container->get('cdn');

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Получает постеры для аниме и сохраняет в ханилище.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $animes = $this->db->animes
            ->find()
            ->toArray();

        $count = 0;
        $ids = array_column($animes, 'shikimori_id');

        $output->writeln("Сохраняю постеры...");

        foreach($ids as $id) {
           $anime = $this->sdk->anime()->findOrFail($id);

            $output->write(
                "\r". ($count++) . '/' . count($ids) . ' '
                . str_pad(
                    $id,
                    50,
                    ' ',
                    STR_PAD_LEFT
                )
            );

            /*$success = $this->cdn->writeStream(
                "/public/anime/{$id}/poster_original",
                $anime->getPoster()->detach()
            );*/

            /*if(!$success) {
                throw new RuntimeException('Error writing poster');
            }*/

            $this->db->animes->updateOne(
                [
                    'shikimori_id' => $id
                ], [
                    '$set' => [
                        'poster' => [
                            'original' => "/anime/{$id}/poster_original"
                        ]
                    ]
                ]
            );
        }

        return 0;
    }
}