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
     * @var string
     */
    private $tablename;

    /**
     * ClickHouseRepository constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $dbname
     * @param string $tablename
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $host,
        int $port,
        string $dbname = 'teststand',
        string $tablename = 'teststand',
        string $username = 'default',
        string $password = ''
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->tablename = $tablename;
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

    /**
     * @param array $header
     * @param $data
     *
     * @return float
     */
    public function write(array $header, $data): float
    {
        $start = microtime(true);
        $this->db->insert($this->tablename,
            $data,
            $header
        );
        $end = microtime(true) - $start;

        return $end;
    }

    /**
     * @return array
     */
    public function read(): array
    {
        $totalResults = [];

        // 1 запрос
        $start = microtime(true);
        $this->db->select("select DayofWeek, avg(DestAirportID) from $this->tablename group by DayofWeek");
        $end = microtime(true) - $start;
        $totalResults[] = $end;

        // 2 запрос
        $start = microtime(true);
        $this->db->select("select OriginStateName count(DayofWeek) from $this->tablename OriginStateName = 'California' group by OriginStateName");
        $end = microtime(true) - $start;
        $totalResults[] = $end;

        // 3 запрос
        $start = microtime(true);
        $this->db->select("select FlightDate sum(DestAirportID) from $this->tablename AirlineID <= 19805 and AirlineID > 5344 group by FlightDate");
        $end = microtime(true) - $start;
        $totalResults[] = $end;

        return $totalResults;
    }

    private function createDatabase(): void
    {
        $this->db->write(sprintf("CREATE DATABASE IF NOT EXISTS %s", $this->dbname));
        $this->db->database($this->dbname);
    }

    private function createTable(): void
    {
        $this->db->write("
                    CREATE TABLE IF NOT EXISTS $this->tablename (                  
                        Year Int32,
                        Quarter Int32,
                        Month Int32,
                        DayofMonth Int32,
                        DayOfWeek Int32,
                        FlightDate Date,
                        AirlineID Int32,
                        TailNum String,
                        FlightNum Int32,
                        OriginAirportID Int32,
                        OriginAirportSeqID Int32,
                        OriginCityMarketID Int32,
                        OriginCityName String,
                        OriginStateName String,
                        DestAirportID Int32,
                        DestAirportSeqID Int32,
                        DestCityMarketID Int32                       
                    ) ENGINE = MergeTree(FlightDate, (TailNum, FlightDate),8129)         
                          ");
    }
}