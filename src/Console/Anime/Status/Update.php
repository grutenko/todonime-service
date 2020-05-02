<?php


namespace App\Console\Anime\Status;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Generator;
use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command
{
    /**
     * @var string
     */
    public static $defaultName = "anime:status.update";

    /**
     * @var Sdk
     */
    private $sdk;

    /**
     * @var Database
     */
    private $db;

    /**
     * Update constructor.
     * @param Container $container
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->sdk = $container->get('shikimori_sdk');
        $this->db = $container->get('mongodb')->todonime;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Обновляет статусы для аниме синхронизируя их с сервисом shikimori.one')
            ->addArgument(
                'animes',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                "Список ID аниме для которых нужно обновить статус."
            )
            ->addOption(
                'for-status',
                null,
                InputOption::VALUE_OPTIONAL,
                'обновлчет статусы для аниме с этим статусами.'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $animesIds = $input->getArgument('animes');
        $statuses = explode(',', $input->getOption('for-status'));

        $this->output = $output;

        if(count($animesIds) == 0) {
            if(in_array('all', $statuses)) {
                $this->updateStatuses();
            } else
            {
                $this->updateStatuses(null, $statuses);
            }
        } else
        {
            $this->updateStatuses($animesIds);
        }

        $output->writeln("\ndone.");
        return 0;
    }

    /**
     * @param array|null $ids
     * @param array|null $statuses
     */
    private function updateStatuses(?array $ids = null, ?array $statuses = null)
    {
        if(!is_array($ids) || $ids == null) {
            if($statuses == null) {
                $this->output->write('Получаю все ID аниме в базе... ');
            } else
            {
                $this->output->write('Получаю все ID аниме в базе для '. implode(', ', $statuses).' ... ');
            }

            $filter = $statuses == null ? [] : ['status' => [ '$in' => $statuses]];

            $animes = $this->db->animes
                ->find($filter)
                ->toArray();

            $ids = array_map(function($anime) {
                if(!isset($anime['shikimori_id'])) {
                    throw new \Exception(print_r($anime, true));
                }

                return $anime['shikimori_id'];
            }, $animes);

            $this->output->writeln(count($ids));
        }

        $count = 0;
        foreach($this->chunkGetStatuses($ids) as $statusChunk) {
            foreach($statusChunk as $status) {
                $this->db->animes->updateMany(
                    ['shikimori_id' => $status['id']],
                    ['$set' => [
                        'status' => $status['status']
                    ]]
                );

                $count++;
                $this->output->write(
                    "\rОбновляю статусы: " . $count . '/' . count($ids) . ' '
                    . str_pad(
                        substr( $statusChunk[0]['name'], 0, 30),
                        30,
                        ' ',
                        STR_PAD_LEFT
                    )
                );
            }
        }
    }

    /**
     * @param array $ids
     * @return Generator
     */
    private function chunkGetStatuses(array $ids): Generator
    {
        foreach(array_chunk($ids, 50) as $chunk) {
            usleep(500000);
            $animes = $this->sdk->anime()->list(['ids' => $chunk, 'limit' => 50]);

            yield array_map(function($anime) {
                return [
                    'id' => $anime->id,
                    'status' => $anime->status,
                    'name' => $anime->name
                ];
            }, $animes->toArray());
        }
    }
}