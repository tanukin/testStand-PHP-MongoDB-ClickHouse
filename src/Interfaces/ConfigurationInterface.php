<?php

namespace Otus\TestStand\Core;

use Otus\TestStand\Exceptions\EmptyContentException;

interface ConfigurationInterface
{
    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getHost(string $typeDb): string;

    /**
     * @param string $typeDb
     *
     * @return int
     *
     * @throws EmptyContentException
     */
    public function getPort(string $typeDb): int;

    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getDbName(string $typeDb): string;

    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getCollection(string $typeDb): string;

    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getUserName(string $typeDb): string;

    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getPassword(string $typeDb): string;

    /**
     * @param string $typeDb
     *
     * @return string
     *
     * @throws EmptyContentException
     */
    public function getTableName(string $typeDb): string;
}