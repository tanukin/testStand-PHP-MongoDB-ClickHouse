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
     * @var Client
     */
    private $db;

    /**
     * MongoRepository constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $dbName
     */
    public function __construct(string $host, int $port, string $dbname = 'teststand')
    {
        $this->host = $host;
        $this->port = $port;
        $this->dbname = $dbname;
    }

    /**
     * @return RepositoryInterface
     */
    public function getConnect(): RepositoryInterface
    {
        if(!isset($this->db))
            $this->db = new Client(sprintf("mongodb://%s:%d", $this->host, $this->port));

        return $this;
    }

    public function write(array $data): float
    {
        $db = $this->db->selectDatabase($this->dbname);

        $start = microtime();
        $db->new->insertMany([
        [
            'item' => 'canvas',
            'qty' => 100,
            'size' => ['h' => 28, 'w' => 35.5, 'uom' => 'cm'],
            'status' => 'A',
        ],
        [
            'item' => 'journal',
            'qty' => 25,
            'size' => ['h' => 14, 'w' => 21, 'uom' => 'cm'],
            'status' => 'A',
        ],
        [
            'item' => 'mat',
            'qty' => 85,
            'size' => ['h' => 27.9, 'w' => 35.5, 'uom' => 'cm'],
            'status' => 'A',
        ],
        [
            'item' => 'mousepad',
            'qty' => 25,
            'size' => ['h' => 19, 'w' => 22.85, 'uom' => 'cm'],
            'status' => 'P',
        ],
        [
            'item' => 'notebook',
            'qty' => 50,
            'size' => ['h' => 8.5, 'w' => 11, 'uom' => 'in'],
            'status' => 'P',
        ],
        [
            'item' => 'paper',
            'qty' => 100,
            'size' => ['h' => 8.5, 'w' => 11, 'uom' => 'in'],
            'status' => 'D',
        ],
        [
            'item' => 'planner',
            'qty' => 75,
            'size' => ['h' => 22.85, 'w' => 30, 'uom' => 'cm'],
            'status' => 'D',
        ],
        [
            'item' => 'postcard',
            'qty' => 45,
            'size' => ['h' => 10, 'w' => 15.25, 'uom' => 'cm'],
            'status' => 'A',
        ],
        [
            'item' => 'sketchbook',
            'qty' => 80,
            'size' => ['h' => 14, 'w' => 21, 'uom' => 'cm'],
            'status' => 'A',
        ],
        [
            'item' => 'sketch pad',
            'qty' => 95,
            'size' => ['h' => 22.85, 'w' => 30.5, 'uom' => 'cm'],
            'status' => 'A',
        ],
    ]);
        $end = microtime() - $start;

        return $end;
    }

    public function read(): float
    {
        $db = $this->db->selectDatabase($this->dbname);

        $start = microtime();
        $db->new->aggregate(
            [
                ['$match' => ['status' => 'A']],
                ['$group' => [
                    '_id' => 'qty',
                    'total' => ['$sum' => '$qty']]]
            ]
        );

        $end = microtime() - $start;

        return $end;
    }
}