<?php
declare(ticks = 1);

namespace App\Lib\Queue;


use MongoDB\Client;
use MongoDB\Collection;

class Daemon
{
    /**
     * @var Collection
     */
    public $collection;

    /**
     * @var array
     */
    public $workers = [];

    /**
     * Daemon constructor.
     * @param Collection $collection
     * @param array $workers
     */
    public function __construct(Collection $collection, array $workers)
    {
        $this->collection = $collection;
        $this->workers = $workers;
    }

    /**
     * @param $config
     * @return bool
     */
    private function checkConfig($config): bool
    {
        $required = [
            'mongodb',
            'db_collection',
            'workers'
        ];
        return array_intersect($required, array_keys($config)) != count($required);
    }

    /**
     *
     */
    public function run()
    {
        $this->listen();
    }

    /**
     * @return void
     */
    private function listen()
    {
        $quit = false;
        $currentID = null;

        pcntl_signal(SIGTERM, function() use (&$quit){
            $quit = true;
        });

        while(!$quit)
        {
            $jobs = $this->getJobs();
            if( count($jobs) == 0 ) {
                usleep(100000);
                continue;
            }

            foreach($jobs as $job)
            {
                if( $this->hasWorker( $job['worker']) ) {
                    $this->getworker($job['worker'])->handle($job);
                }
            }

            $this->deleteJobs($jobs);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasWorker(string $name): bool
    {
        return isset($this->workers[ $name ]);
    }

    /**
     * @param string $name
     * @return WorkerInterface
     */
    private function getWorker( string $name ): WorkerInterface
    {
        return $this->workers[ $name ];
    }

    /**
     * @return array
     */
    private function getJobs(): array
    {
        return $this->collection->aggregate([
            ['$sort' => [
                '_id' => 1
            ]]
        ])->toArray();
    }

    /**
     * @param array $jobs
     * @return void
     */
    private function deleteJobs(array $jobs)
    {
        $this->collection->deleteMany([
            '_id' => [
                '$in' => array_column($jobs, '_id')
            ]
        ]);
    }
}