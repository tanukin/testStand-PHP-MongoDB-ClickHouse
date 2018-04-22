<?php

namespace Otus\TestStand\Core;

use Otus\TestStand\Exceptions\EmptyContentException;

class ParseCsv
{
    /**
     * @var string
     */
    private $path;

    private $header;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param int $step
     *
     * @return \Generator
     *
     * @throws EmptyContentException
     */
    public function parse(int $step)
    {
        if (!file_exists($this->path))
            throw new EmptyContentException("Configuration file not found");

        $handle = fopen($this->path, "r");

        if (!isset($this->header)) {
            $this->header = array_map(function ($item) {
                return sprintf('"%s"', trim($item));
            }, fgetcsv($handle, 1000, ";"));
        }

        while (!feof($handle)) {
            $rows = [];

            for ($i = 0; $i < $step; $i++) {
                $row = fgetcsv($handle, 1000, ";");

                if (empty($row))
                    break;

                $row = array_map(function ($item) {
                    if (preg_match("~[a-zA-Z-]~", $item))
                        return $item;

                    if (strpos($item, '.') !== false)
                        return (float)$item;

                    return (int)$item;
                }, $row);

                array_push($rows, $row);
            }

            yield $rows;
        }
        fclose($handle);
    }

    public function getHeader(): array
    {
        return $this->header;
    }


}