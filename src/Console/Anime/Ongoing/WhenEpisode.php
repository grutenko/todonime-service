<?php


namespace App\Console\Anime\Ongoing;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Grutenko\Shikimori\Sdk;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WhenEpisode extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'anime:ongoing.episode.when';

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
            ->setDescription('Обновляет информацию о следующей серии оногоинга.')
            ->addArgument(
                'animes',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                "Список ID аниме для которых нужно обновить дату следующей серии."
            )
            ->addOption(
                'skip-status-update',
                null,
                InputOption::VALUE_OPTIONAL,
                'Пропускает этап обновления статусов онгоингов и анонсов.',
                'n'
            )
            ->addOption(
                'skip',
                null,
                InputOption::VALUE_OPTIONAL,
                'Пропускает из выборки n аниме. Можно продолжить с того же места где случилась ошибка.',
                0
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isSkip = $input->getOption('skip-status-update');
        $ids = $input->getArgument('animes');
        $skip = $input->getOption('skip');

        if(count($ids) == 0) {
            $animes = $this->db->animes->find(
                [
                    'status' => 'ongoing',
                    '$or' => [
                        ['next_episode_at' => ['$exists' => false]],
                        ['next_episode_at' => null],
                        ['next_episode_at' => ['$gt' => new UTCDateTime()]]
                    ]
                ])->toArray();
            $ids = array_column($animes, 'shikimori_id');
        }

        if( $isSkip == 'n' ) {
            if(count($ids) == 0) {
                $params = [
                    'command' => 'anime:status.update',
                    '--for' => 'anons,ongoing'
                ];
            } else
            {
                $params = [
                    'command' => 'anime:status.update',
                    'animes' => $ids
                ];
            }

            $code = $this
                ->getApplication()
                ->find('anime:status.update')
                ->run(new ArrayInput($params), $output);

            if($code != 0) {
                return $code;
            }
        }

        $count = 0;
        $skipped = 0;
        $updated = 0;

        if(count($ids) == 0) {
            $ids = $this->getAnimeIdsForOngoings();
        }

        $size = count($ids);
        if($skip > 0) {
            $ids = array_slice($ids, $skip - count($ids));
            $count = $skip;
            $size = count($ids) + $skip;
        }

        foreach($ids as $id) {
            usleep(500000);
            try {
                $anime = $this->sdk->anime()->find($id);
            } catch(\Exception $e) {
                $output->writeln('');
                $output->writeln("<info> {$count} (updated: {$updated}; skipped: {$skipped}) error.</info>");
                throw $e;
            }

            if($anime == null) {
                continue;
            }

            $count++;
            $output->write("\rОбновляю даты следующих серий для всех онгоингов: {$count}/".$size. ' skipped: '. $skipped . ' '
                . str_pad(
                    substr( $anime->name, 0, 50),
                    50,
                    ' ',
                    STR_PAD_LEFT
                )
            );

            if($anime == null) {
                $skipped++;
                continue;
            }

            $time = strtotime($anime->next_episode_at);
            if($time === false) {
                $skipped++;
                continue;
            }

            $this->db->animes->updateMany(
                ['shikimori_id' => $id],
                ['$set' => [
                    'next_episode_at' => new UTCDateTime($time * 1000)
                ]]
            );
            $updated++;
        }

        $output->writeln('');
        $output->writeln("<info> {$count} (updated: {$updated}; skipped: {$skipped}) done.</info>");
        return 0;
    }

    /**
     * @return array
     */
    private function getAnimeIdsForOngoings(): array
    {
        $animes = $this->db->animes
            ->find(['status' => ['$in' => ['ongoing', 'anons']]])
            ->toArray();

        return array_column($animes, 'shikimori_id');
    }
}