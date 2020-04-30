<?php


namespace App\Console;


use Exception;
use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetAnimes extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:get';

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
     *
     */
    protected function configure()
    {
        $this->setDescription('Управляет полученим списка аниме из shikimori.one');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastAnime = $this->db->animes
            ->find([], ['sort' => ['shikimori_id' => -1], 'limit' => 1])
            ->toArray();

        $lastMyShikiId = count($lastAnime) > 0
            ? $lastAnime[0]['shikimori_id'] + 1
            : 0;

        $tryGetList = 0;

        $output->writeln("Последний записаный ID: {$lastMyShikiId}");

        $rangeChunk = array_chunk(range($lastMyShikiId, 100000), 50);
        foreach ($rangeChunk as $chunk) {

            $items = $this->sdk->anime()->list(['ids' => $chunk, 'limit' => 50, 'order' => 'id']);
            if ($items->count() == 0) {
                if ($tryGetList < 20) {
                    $tryGetList++;
                    $output->write("\r" . 'Страница пустая. Попытка получить следующую #' . $tryGetList . '/' . 20);
                    continue;
                } else {
                    $output->writeln('');
                    break;
                }
            }

            foreach ($items as $item) {
                $output->writeln("\r" . trim($item->__toString()));
            }

            $this->db->animes->insertMany(array_map(function ($anime) {
                return [
                    'shikimori_id' => $anime->id,
                    'name_en' => $anime->name,
                    'name_ru' => $anime->russian
                ];
            }, $items->toArray()));
        }

        $output->writeln('\nDone.');

        return 0;
    }
}