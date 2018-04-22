<?php

namespace Otus\TestStand\Commands;

use Otus\TestStand\Core\Configuration;
use Otus\TestStand\Core\ParseCsv;
use Otus\TestStand\Core\TestStandHelper;
use Otus\TestStand\Exceptions\EmptyContentException;
use Otus\TestStand\Repository\ClickHouseRepository;
use Otus\TestStand\Repository\MongoRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestStandCommand extends Command
{
    const CASES = [
        ["file" => "100.csv", "step" => 100, "rows" => 100],
        ["file" => "1000.csv", "step" => 1000, "rows" => 1000],
        ["file" => "10000.csv", "step" => 1000, "rows" => 10000],
        ["file" => "100000.csv", "step" => 1000, "rows" => 100000],
        ["file" => "1000000.csv", "step" => 1000, "rows" => 1000000],
    ];

    protected function configure()
    {
        $this
            ->setName('app:TestStand')
            ->setDescription(
                'Test bench for performance comparison aggregation requests in MongoDB and ClickHouse.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        try {

            $config = new Configuration(__DIR__ . "/../Config/config.yml");

            $clickHouse = new ClickHouseRepository(
                $config->getHost(Configuration::CLICKHOUSE),
                $config->getPort(Configuration::CLICKHOUSE),
                $config->getDbName(Configuration::CLICKHOUSE)
            );

            $mongoDB = new MongoRepository(
                $config->getHost(Configuration::MONGO),
                $config->getPort(Configuration::MONGO),
                $config->getDbName(Configuration::MONGO)
            );

            foreach (self::CASES as $case) {
                $mongoResult = [];
                $clickHouseResult = [];
                $csv = new ParseCsv(sprintf("%s/../Data/%s", __DIR__, $case["file"]));

                foreach ($csv->parse($case["step"]) as $data) {
                    $header = $csv->getHeader();
                    array_push($mongoResult, $mongoDB->getConnect()->write($header, $data));
                    array_push($clickHouseResult, $clickHouse->getConnect()->write($header, $data));
                }

                $data[] = [
                    Configuration::MONGO => array_sum($mongoResult),
                    Configuration::CLICKHOUSE => array_sum($clickHouseResult)
                ];

                unset($csv);
            }

            $helper = new TestStandHelper();
            $this->echoTable($io,"Time of recording in the database", $this->prepareResult($helper, $data)
            );
            $io->text(sprintf('Total: The shortest time for writing in the database - %s\n', $helper->getLeader()));
            $helper->clearCounter();

        } catch (EmptyContentException $e) {
            $io->warning(sprintf('ERROR! %s', $e->getMessage()));
        }
    }

    protected function echoTable(SymfonyStyle $io, string $title, array $data): void
    {
        $io->title($title);
        $io->table(
            array('Record count', 'Mongo', 'ClickHouse', 'Difference', 'Faster'),
            $data
        );
    }

    /**
     * @param TestStandHelper $helper
     * @param array $data
     *
     * @return array
     */
    protected function prepareResult(TestStandHelper $helper, array $data = []): array
    {
        $dataResult = [];

        foreach (self::CASES as $case) {
            $arr = [];
            $result = array_shift($data);

            $mongo = $result[Configuration::MONGO];
            $clickHouse = $result[Configuration::CLICKHOUSE];

            array_push($arr, $case["rows"]);
            array_push($arr, $mongo);
            array_push($arr, $clickHouse);
            array_push($arr, $helper->getDifferenceTime($mongo, $clickHouse));
            array_push($arr, $helper->getFaster($mongo, $clickHouse));

            array_push($dataResult, $arr);
        }

        return $dataResult;
    }
}