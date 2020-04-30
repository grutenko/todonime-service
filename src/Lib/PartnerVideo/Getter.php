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
     * Запускает получение видео из $this->getters
     */
    public function run()
    {
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
            }

            $this->output->writeln("\ndone.");
        }
    }
}