<?php


namespace App\Console\User;


use Grutenko\Shikimori\Sdk;
use MongoDB\Database;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetUserRates extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'user:rates.get';

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
        $this
            ->setDescription('Обновляет информацию о просмотренном пользователя.')
            ->addArgument(
                'names',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                "Ники пользователей для которых нужно обновить данные."
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $names = $input->getArgument('names');

        if( is_array($names) && count($names) > 0 )
        {
            $users = $this->db->users->find(
                [
                    'nickname' => ['$in' => $names]
                ],
                [
                    '_id' => 1
                ]
            )->toArray();
        }
        else
        {
            $users = $this->db->users->find(
                [],
                [
                    '_id'           => 1,
                    'nickname'      => 1,
                    'shikimori_id'  => 1,
                    'token'         => 1
                ]
            )->toArray();
        }

        foreach($users as $user)
        {
            if(!isset($user['token']))
            {
                continue;
            }

            $output->write($user['nickname']);

            $this->sdk->useOauthToken($user['token'], function($newToken) use($user) {
                $this->db->users->updateOne(
                    [
                        '_id' => $user['_id']
                    ],
                    ['$set' => [
                        'token' => $newToken
                    ]]
                );
            });

            $rates = $this->sdk->user()->getRates($user['shikimori_id']);
            if($rates == null)
            {
                continue;
            }

            $episodes = [];
            foreach($rates as $rate) {
                if ($rate['target_type'] != 'Anime') {
                    continue;
                }

                $anime = $this->db->animes->findOne([
                    'shikimori_id' => $rate['target_id']
                ]);

                if ($anime == null) {
                    continue;
                }

                $episodes[] = [
                    'anime_id' => $anime['_id'],
                    'episodes' => $rate['episodes']
                ];
            }

            $this->db->users->updateOne(
                [
                    '_id' => $user['_id']
                ],
                ['$set' => [
                    'watched_episodes' => $episodes
                ]]
            );

            $output->writeln('<info> done.</info>');
        }

        return 0;
    }
}