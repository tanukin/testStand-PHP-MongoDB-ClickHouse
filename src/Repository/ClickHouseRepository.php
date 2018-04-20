<?php

namespace Otus\TestStand\Repository;

use ClickHouseDB\Client;
use Otus\TestStand\Interfaces\RepositoryInterface;

class ClickHouseRepository implements RepositoryInterface
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
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Client
     */
    private $db;

    /**
     * ClickHouseRepository constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $dbname
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $host,
        int $port,
        string $dbname = 'teststand',
        string $username = 'default',
        string $password = ''
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return RepositoryInterface
     */
    public function getConnect(): RepositoryInterface
    {
        if (!isset($this->db)) {
            $this->db = new Client([
                'host' => $this->host,
                'port' => $this->port,
                'username' => $this->username,
                'password' => $this->password
            ]);

            $this->createDatabase();
            $this->createTable();
        }

        return $this;
    }

    public function write(array $data): float
    {
        $start = microtime();

        $this->db->insert('summing_url_views',
            [
                [time(), 'HASH1', 2345, 22, 20, 2],
                [time(), 'HASH2', 2345, 12, 9, 3],
                [time(), 'HASH3', 5345, 33, 33, 0],
                [time(), 'HASH3', 5345, 55, 0, 55],
            ],
            ['event_time', 'site_key', 'site_id', 'views', 'v_00', 'v_55']
        );

        $end = microtime() - $start;

        return $end;
    }

    public function read(): float
    {
        $start = microtime();

        $statement = $this->db->select('
                            SELECT event_date, site_key, sum(views), avg(views)
                            FROM summing_url_views
                            WHERE site_id < 3333
                            GROUP BY event_date, url_hash
                            WITH TOTALS
                        ');

        $end = microtime() - $start;

        return $end;
    }

    private function createDatabase(): void
    {
        $this->db->write(sprintf("CREATE DATABASE IF NOT EXISTS %s", $this->dbname));
        $this->db->database($this->dbname);
    }

    private function createTable(): void
    {
        $this->db->write('
    CREATE TABLE IF NOT EXISTS summing_url_views (
        event_date Date DEFAULT toDate(event_time),
        event_time DateTime,
        site_id Int32,
        site_key String,
        views Int32,
        v_00 Int32,
        v_55 Int32
    )
    ENGINE = SummingMergeTree(event_date, (site_id, site_key, event_time, event_date), 8192)
');
    }
}