<?php


namespace App\Console;


use App\Lib\PartnerVideo\Getter;
use App\Lib\PartnerVideo\Getters\SmotretAnimeGetter;
use App\Lib\SmotretAnimeApi;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetVideos extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'video:get';

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
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Получает и сохраняет видео от партнеров.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $getters = [
            new SmotretAnimeGetter(new SmotretAnimeApi)
        ];
        (new Getter($getters, $this->db, $output))
            ->run();

        return 0;
    }
}