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
     * @param array $params
     * @return ObjectId
     */
    public function add(array $params)
    {
        $this->collection->insertOne($params);
    }
}