<?php


namespace App\Console\Video\Binary;


use DI\Container;
use FFMpeg\FFMpeg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function DI\create;

class CreateScreenShots extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'video:binary.screenshots';

    public function __construct(Container $container)
    {
        $this->db = $container->get('mongodb')->todonime->subs;
        $this->storage = $container->get('cdn');

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Создает и сохраняет скриншоты для видео.')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'n'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force') == 'y';

        /**
         * Путь до корня публичной части Flysystem
         */
        $path = $_ENV['PUBLIC_STORAGE_DIR'] ?: realpath(__DIR__ . '/../storage/public');

        $dbVideos = $this->db->find()->toArray();

        foreach($dbVideos as $dbVideo)
        {
            if( isset($dbVideo['screenshots']) && !$force )
            {
                continue;
            }

            if( !file_exists($path. '/thumbnails/'. $dbVideo['anime_id']) )
            {
                mkdir($path. '/thumbnails/'. $dbVideo['anime_id']);
            }

            $thumbPath = '/thumbnails/'. $dbVideo['anime_id']. '/'.$dbVideo['episode']. '_%d.jpg';
            echo `ffmpeg -i {$path}{$dbVideo['video']} -vf fps=1/600 {$path}{$thumbPath}`;

            $screenshots = [];
            foreach(scandir( dirname($path.$thumbPath) ) as $name)
            {
                if( in_array($name, ['.', '..']) )
                {
                    continue;
                }

                $screenshots[] = dirname($thumbPath).'/'. $name;
            }

            $this->db->updateOne(['_id' => $dbVideo['_id']], [
                '$set' => [
                    'screenshots' => $screenshots
                ]]);

            $output->writeln($dbVideo['_id']->__toString() . ' done.');
        }

        return 0;
    }
}