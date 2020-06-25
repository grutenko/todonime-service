<?php


namespace App\Console\Binary;


use App\Console\TodonimeCommand;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Delete extends TodonimeCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'binary:delete';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Удаляет видео по binary ID')
            ->addUsage('binary:delete 5ea8cf118ae77bfc83061675')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Binary video ID'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $storage = $_ENV['PUBLIC_STORAGE_DIR'] ?: '/srv/www/todonime/storage/public';
        $binary = $this->container->get('mongodb')->todonime->binaries->findOne([ '_id' =>  new ObjectId($id) ]);

        if(!$binary)
        {
            throw new \RuntimeException("Видео {$id} не найдено.");
        }

        if( isset($binary['video']) && file_exists($storage . $binary['video']) )
        {
            unlink($storage . $binary['video'] );
        }
        if( isset($binary['preview']) && file_exists($storage . $binary['preview']))
        {
            unlink($storage . $binary['preview'] );
        }
        if( isset($binary['screenshots']) )
        {
            foreach($binary['screenshots'] as $screenshot)
            {
                if(file_exists($storage . $screenshot['path']))
                {
                    unlink($storage . $screenshot['path']);
                }
            }
        }

        $this->container->get('mongodb')->todonime->binaries->deleteOne(['_id' => $binary['_id']]);

        return 0;
    }
}