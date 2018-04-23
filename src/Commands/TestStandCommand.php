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

            // Тесты на запись данных
            foreach (self::CASES as $case) {
                $mongoResult = [];
                $clickHouseResult = [];
                $csv = new ParseCsv(sprintf("%s/../Data/%s", __DIR__, $case["file"]));

                foreach ($csv->parse($case["step"]) as $data) {
                    $header = $csv->getHeader();
                    array_push($mongoResult, $mongoDB->getConnect()->write($header, $data));
                    array_push($clickHouseResult, $clickHouse->getConnect()->write($header, $data));
                }

                $totalResults[] = [
                    Configuration::MONGO => array_sum($mongoResult),
                    Configuration::CLICKHOUSE => array_sum($clickHouseResult)
                ];

                unset($csv);
            }

            $helper = new TestStandHelper();

            $tests = [];
            foreach (self::CASES as $case)
                array_push($tests, $case['rows']);

            $this->echoTable(
                $io,
                "Time of recording in the database",
                array('Record count', 'Mongo', 'ClickHouse', 'Difference', 'Faster'),
                $this->prepareResult($helper, $tests, $totalResults));
            $io->text(sprintf('Total: The shortest time for writing in the database - %s', $helper->getLeader()));
            $helper->clearCounter();


            //Тесты на чтение данных
            $mongo = $mongoDB->getConnect()->read();
            $clickHouse = $clickHouse->getConnect()->read();
            $totalResults = [];
            $tests = [];

            for ($i = 0; $i < 3; $i++) {
                array_push($totalResults, [
                    Configuration::MONGO => $mongo[$i],
                    Configuration::CLICKHOUSE => $clickHouse[$i]
                ]);
                array_push($tests, $i + 1);
            }

            $this->echoTable(
                $io,
                "Time reading from the database",
                array('SQL query number', 'Mongo', 'ClickHouse', 'Difference', 'Faster'),
                $this->prepareResult($helper, $tests, $totalResults));
            $io->text(sprintf('Total: The shortest time for reading in the database - %s', $helper->getLeader()));


        } catch (EmptyContentException $e) {
            $io->warning(sprintf('ERROR! %s', $e->getMessage()));
        }
    }

    protected function echoTable(SymfonyStyle $io, string $title, array $headers, array $data): void
    {
        $io->title($title);
        $io->table(
            $headers,
            $data
        );
    }

    /**
     * @param TestStandHelper $helper
     * @param array $tests
     * @param array $data
     *
     * @return array
     */
    protected function prepareResult(TestStandHelper $helper, array $tests, array $data): array
    {
        $dataResult = [];

        foreach ($tests as $case) {
            $arr = [];
            $result = array_shift($data);

            $mongo = $result[Configuration::MONGO];
            $clickHouse = $result[Configuration::CLICKHOUSE];

            array_push($arr, $case);
            array_push($arr, $mongo);
            array_push($arr, $clickHouse);
            array_push($arr, $helper->getDifferenceTime($mongo, $clickHouse));
            array_push($arr, $helper->getFaster($mongo, $clickHouse));

            array_push($dataResult, $arr);
        }

        return $dataResult;
    }
}