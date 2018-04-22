<?php

namespace Otus\TestStand\Core;

use Otus\TestStand\Exceptions\EmptyContentException;
use Otus\TestStand\Interfaces\ConfigurationInterface;
use Symfony\Component\Yaml\Yaml;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $path;

    const MONGO = "mongo";
    const CLICKHOUSE = "clickhouse";

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(string $typeDb): string
    {
        return $this->getValue($typeDb, 'host');
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(string $typeDb): int
    {
        return $this->getValue($typeDb, 'port');
    }

    /**
     * {@inheritdoc}
     */
    public function getDbName(string $typeDb): string
    {
        return $this->getValue($typeDb, 'dbname');
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $typeDb): string
    {
        return $this->getValue($typeDb, 'collection');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName(string $typeDb): string
    {
        return $this->getValue($typeDb, 'username');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(string $typeDb): string
    {
        return $this->getValue($typeDb, 'password');
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName(string $typeDb): string
    {
        return $this->getValue($typeDb, 'tablename');
    }

    /**
     * @param string $typeDb
     * @param string $params
     *
     * @return mixed
     *
     * @throws EmptyContentException
     */
    protected function getValue(string $typeDb, string $params)
    {
        switch ($typeDb) {
            case self::MONGO:
                return $this->getAllSettings()[self::MONGO][$params];
            case self::CLICKHOUSE:
                return $this->getAllSettings()[self::CLICKHOUSE][$params];
        }

        throw new EmptyContentException(sprintf("Not found value for %s, type database %s", $params, $typeDb));
    }

    /**
     * @return mixed
     *
     * @throws EmptyContentException
     */
    protected function getAllSettings()
    {
        if (!file_exists($this->path))
            throw new EmptyContentException("Configuration file not found");

        $content = Yaml::parseFile($this->path);

        if (empty($content))
            throw new EmptyContentException("Configuration file is empty");

        return $content;
    }
}