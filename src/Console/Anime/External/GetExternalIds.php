<?php


namespace App\Console\Anime\External;


use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetExternalIds extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:external-ids.get';

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
        $this->setDescription('Получает все достпуные внещние ID аниме.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $anime_ids = $this->db->animes->distinct('shikimori_id');
    }
}