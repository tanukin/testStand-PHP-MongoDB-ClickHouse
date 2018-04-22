<?php

namespace Otus\TestStand\Core;

class TestStandHelper
{
    const MONGO = "Mongo";
    const CLICKHOUSE = "ClickHouse";

    /**
     * @var int
     */
    private $countMongo = 0;

    /**
     * @var int
     */
    private $countClickHouse = 0;

    public function getDifferenceTime(float $mongo, float $clickHouse): float
    {
        if ($mongo > $clickHouse) {
            $this->countClickHouse++;

            return $mongo - $clickHouse;
        }
        $this->countMongo++;

        return $clickHouse - $mongo;
    }

    public function getFaster(float $mongo, float $clickHouse): string
    {
        if ($mongo > $clickHouse)
            return self::CLICKHOUSE;

        return self::MONGO;
    }

    public function clearCounter(): void
    {
        $this->countMongo = 0;
        $this->countClickHouse = 0;
    }

    public function getLeader(): string
    {
        return $this->countMongo > $this->countClickHouse ? self::MONGO : self::CLICKHOUSE;
    }
}