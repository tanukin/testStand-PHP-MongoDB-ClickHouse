<?php

namespace Otus\TestStand\Repository;

use MongoDB\Client;
use Otus\TestStand\Interfaces\RepositoryInterface;

class MongoRepository implements RepositoryInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $dbname;

    /**
     * @var string
     */
    private $collection;

    /**
     * @var Client
     */
    private $db;

    /**
     * MongoRepository constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $dbname
     * @param string $collection
     */
    public function __construct(
        string $host,
        int $port,
        string $dbname = 'teststand',
        string $collection = 'teststand'
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->dbname = $dbname;
        $this->collection = $collection;
    }

    /**
     * @return RepositoryInterface
     */
    public function getConnect(): RepositoryInterface
    {
        if (!isset($this->db))
            $this->db = new Client(sprintf('mongodb://%s:%d', $this->host, $this->port));

        return $this;
    }

    /**
     * @param array $header
     * @param $data
     *
     * @return float
     */
    public function write(array $header, $data): float
    {
        $db = $this->db->selectDatabase($this->dbname)->selectCollection($this->collection);

        $arrInsert[] = array_map(function ($item) use ($header) {
            return array_combine($header, $item);
        }, $data);

        $start = microtime(true);
        $db->insertMany($arrInsert);
        $end = microtime(true) - $start;

        return $end;
    }

    /**
     * @return float
     */
    public function read(): float
    {
        $db = $this->db->selectDatabase($this->dbname)->selectCollection($this->collection);

        $start = microtime(true);
        /*$db->aggregate(
            [
                ['$match' => ['status' => 'A']],
                ['$group' => [
                    '_id' => 'qty',
                    'total' => ['$sum' => '$qty']]]
            ]
        );*/

        $end = microtime(true) - $start;

        return $end;
    }
}