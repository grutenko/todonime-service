<?php


namespace App\Lib\Queue;


use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

class Client
{
    /**
     * @var Collection
     */
    public $collection;

    /**
     * Daemon constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param string $workerName
     * @param array $params
     * @return void
     */
    public function add(string $workerName, array $params)
    {
        $params['worker'] = $workerName;
        $this->collection->insertOne($params);
    }
}