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
     * @param $animeId
     * @param $videoIds
     */
    private function updateEpisodesList($animeId, $videoIds)
    {
        $episodes = [];
        foreach($videoIds as $episode => $partnerIds) {
            foreach($partnerIds as $id) {
                $video = $this->db->videos
                    ->findOne(['partner_video_id' => $id]);
                $this->db->episodes
                    ->updateOne(
                        ['_id.anime_id' => $animeId, '_id.episode' => $episode],
                        [
                            '$ddToSet' => [
                                'videos' => [ '_id' => $video->_id, 'author' => $video->author]
                            ]
                        ]
                    );
            }
        }
    }

    /**
     * Запускает получение видео из $this->getters
     */
    public function run()
    {
        $lastEpisodes = [];
        $partnerVideoIds = [];

        /** @var GetterInterface $getter */
        foreach ($this->getters as $getter) {
            $this->output->writeln($getter->getName());

            $lastId = $getter->getLastId();

            foreach ($getter->videoGenerator($this->db) as $chunk) {
                $this->db->videos->insertMany($chunk);

                if (null != $this->output) {
                    $lastVideoId = array_pop($chunk)['partner_video_id'];
                    $this->output->write("\r{$lastVideoId}/{$lastId}");
                }

                foreach($chunk as $video) {
                    if(!isset( $lastEpisodes[ $video['anime_id'] ]) ) {
                        $lastEpisodes[ $video['anime_id'] ] = 0;
                    }
                    if( $lastEpisodes[ $video['anime_id'] ] < $video['episode']) {
                        $lastEpisodes[ $video['anime_id'] ] = $video['episode'];
                    }

                    if( !isset($partnerVideoIds[ $video['anime_id'] ])) {
                        $partnerVideoIds[ $video['anime_id'] ] = [];
                    }
                    $partnerVideoIds[ $video['anime_id'] ][ $video['episode'] ][] = $video['partner_video_id'];
                }
            }

            $this->output->writeln("\nОбновляю данные о последних сериях аниме...");
            foreach($lastEpisodes as $animeId => $lastEpisode) {
                $this->saveNewLastEpisode($animeId, $lastEpisode);
            }

            $this->output->writeln("Обновляю данные о списке доступных видео для серии..");
            foreach($partnerVideoIds as $animeId => $partnerIds) {
                $this->updateEpisodesList($animeId, $partnerIds);
            }

            $this->output->writeln("\ndone.");
        }
    }
}