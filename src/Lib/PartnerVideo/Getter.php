<?php


namespace App\Lib\PartnerVideo;


use MongoDB\Database;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class Getter
{
    /**
     * @var array<GetterInterface>
     */
    private $getters;

    /**
     * @var Output|null
     */
    private $output;

    /**
     * @var Database
     */
    private $db;

    /**
     * Getter constructor.
     * @param array<GetterInterface> $getters
     * @param Database $db
     * @param OutputInterface|null $output
     */
    public function __construct(array $getters, Database $db, ?OutputInterface $output = null)
    {
        $this->getters = $getters;
        $this->db = $db;
        $this->output = $output;
    }

    /**
     * @param $animeId
     * @param $lastEpisode
     */
    private function saveNewLastEpisode($animeId, $lastEpisode)
    {
        $this->db->animes->updateOne(
            ['shikimori_id' => $animeId, 'last_episode' => ['$lt' => $lastEpisode]],
            ['$set' => ['last_episode' => $lastEpisode] ]
        );
    }

    /**
     * Запускает получение видео из $this->getters
     */
    public function run()
    {
        $lastEpisodes = [];
        $insertedIds = [];
        $skipped = 0;
        $writed = 0;

        /** @var GetterInterface $getter */
        foreach ($this->getters as $getter) {
            $this->output->writeln($getter->getName());

            $lastId = $getter->getLastId();

            foreach ($getter->videoGenerator($this->db) as $chunk) {
                foreach($chunk as $video) {
                    if (null != $this->output) {
                        $this->output->write("\r{$video['partner_video_id']}/{$lastId} writed: {$writed}, skipped:{$skipped}");
                    }

                    try {
                        $this->db->videos->insertOne($video);
                    } catch(\Exception $e)
                    {
                        $skipped ++;
                        continue;
                    }

                    $writed++;

                    if(!isset( $lastEpisodes[ $video['anime_id'] ]) ) {
                        $lastEpisodes[ $video['anime_id'] ] = 0;
                    }
                    if( $lastEpisodes[ $video['anime_id'] ] < $video['episode']) {
                        $lastEpisodes[ $video['anime_id'] ] = $video['episode'];
                    }

                    @$insertedIds[ $video['anime_id'] ][ $video['episode'] ][] = $video['partner_video_id'];
                }
            }

            $this->output->writeln("\nОбновляю данные о последних сериях аниме...");
            foreach($lastEpisodes as $animeId => $lastEpisode) {
                $this->saveNewLastEpisode($animeId, $lastEpisode);
            }

            $this->output->writeln("\ndone.");
        }
    }
}