<?php

namespace Otus\TestStand\Interfaces;

interface RepositoryInterface
{
    public function getConnect(): RepositoryInterface;
    public function write(array $header, $data): float;
    public function read(): float;
}