<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use TarfinLabs\Netgsm\Exceptions\ReportException;
use TarfinLabs\Netgsm\Netgsm;
use TarfinLabs\Netgsm\NetgsmSmsReport;

class NetGsmReportTest extends BaseTestCase
{
    /**
     * @var Netgsm
     */
    protected $netgsm;

    /**
     * @var Client
     */
    protected $httpClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(new Client());

        $this->netgsm = new Netgsm($this->httpClient, [
            'user_code' => $this->faker->userName,
            'secret'    => $this->faker->password
        ]);
    }

    /**
     * @param $response
     */
    protected function mockReportApiRequest($response)
    {
        $this->httpClient->shouldReceive('request')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                $response
            ));
    }

    /**
     * @param $version
     * @param $rowCount
     * @return array
     */
    protected function generateResponse($version, $rowCount):array
    {
        $response = [];
        switch ($version) {
            case 0:
            case 1:
                for ($i = 0; $i < $rowCount; $i++) {
                    $item = [
                        $this->faker->numberBetween(11111, 99999),
                        $this->faker->e164PhoneNumber,
                        $this->faker->randomElement([0, 1, 2, 3, 4, 11, 12, 13, 100, 103])
                    ];
                    $response[] = $item;
                }
                break;
            case 2:
                for ($i = 0; $i < $rowCount; $i++) {
                    $item = [
                        $this->faker->numberBetween(11111, 99999),
                        $this->faker->e164PhoneNumber,
                        $this->faker->randomElement([0, 1, 2, 3, 4, 11, 12, 13, 100, 103]),
                        $this->faker->randomElement([10, 20, 30, 40, 50, 60, 70]),
                        $this->faker->randomElement([1, 2]),
                        $this->faker->dateTime->format('d.m.Y'),
                        $this->faker->dateTime->format('H:i:s'),
                        $this->faker->randomElement([
                            0, 101, 102, 103, 104, 105, 106, 111, 112, 113, 114, 115, 116, 117, 119
                        ])
                    ];
                    $response[] = $item;
                }
                break;
            case 3:
                for ($i = 0; $i < $rowCount; $i++) {
                    $item = [
                        $this->faker->numberBetween(11111, 99999),
                        $this->faker->randomElement([0, 1, 2, 3, 4, 11, 12, 13, 100, 103]),
                        $this->faker->randomElement([
                            0, 101, 102, 103, 104, 105, 106, 111, 112, 113, 114, 115, 116, 117, 119
                        ])
                    ];
                    $response[] = $item;
                }
                break;
        }
        return $response;
    }

    /**
     * @param $items
     * @return string|null
     */
    protected function convertRaw($items)
    {
        $raw = [];
        foreach ($items as $item) {
            $raw[] = implode(' ', $item);
        }

        return implode('<br>', $raw);
    }

    /**
     * @param  array  $items
     * @param $version
     * @return Collection
     */
    protected function convertCollection(array $items, int $version): Collection
    {
        $collection = new Collection();
        foreach ($items as $item) {
            $collectionItem = [];
            switch ($version) {
                case 0:
                case 1:
                    $collectionItem['jobId'] = $item[0];
                    $collectionItem['phone'] = $item[1];
                    $collectionItem['status'] = $item[2];
                    break;
                case 2:
                    $collectionItem['jobId'] = $item[0];
                    $collectionItem['phone'] = $item[1];
                    $collectionItem['status'] = $item[2];
                    $collectionItem['operatorCode'] = $item[3];
                    $collectionItem['length'] = $item[4];
                    $collectionItem['startDate'] = $item[5];
                    $collectionItem['startTime'] = $item[6];
                    $collectionItem['errorCode'] = $item[7];
                    break;
                case 3:
                    $collectionItem['jobId'] = $item[0];
                    $collectionItem['status'] = $item[1];
                    $collectionItem['errorCode'] = $item[2];
                    break;
            }
            $collection->push($collectionItem);
        }

        return $collection;
    }

    /**
     * @param  null  $phone
     * @return NetgsmSmsReport
     */
    protected function newSmsReport($phone = null)
    {
        $report = new NetgsmSmsReport();
        $report->setPage(1);

        return $report;
    }

    /**
     * @test
     */
    public function it_does_get_same_row_count_with_correct_arguments()
    {
        $version = $this->faker->randomElement([0, 1, 2, 3]);
        $rowCount = $this->faker->numberBetween(5, 10);
        $report = $this->newSmsReport();
        $response = $this->convertRaw($this->generateResponse($version, $rowCount));
        $this->mockReportApiRequest($response);

        $startDate = new Carbon();
        $endDate = new Carbon();

        $report = $this->netgsm->getReports($report, $startDate, $endDate, [
            'version' => $version
        ]);

        $this->assertSame($report->count(), $rowCount);
    }

    /**
     * @test
     */
    public function should_throw_exception_when_return_code_is_not_success()
    {
        $version = $this->faker->randomElement([0, 1, 2, 3]);
        $report = $this->newSmsReport();
        $errorCode = $this->faker->randomElement([30, 60, 70, 100, 101]);
        $this->mockReportApiRequest($errorCode);

        $startDate = new Carbon();
        $endDate = new Carbon();

        $this->expectException(ReportException::class);
        $this->expectExceptionCode($errorCode);

        $this->netgsm->getReports($report, $startDate, $endDate, [
            'version' => $version
        ]);
    }

    /**
     * @test
     */
    public function response_should_properly_parsed_according_by_version()
    {
        $version = $this->faker->randomElement([2]);
        $rowCount = $this->faker->numberBetween(5, 10);
        $report = $this->newSmsReport();
        $response = $this->generateResponse($version, $rowCount);
        $collection = $this->convertCollection($response, $version)->keyBy('jobId');
        $rawResponse = $this->convertRaw($response);

        $this->mockReportApiRequest($rawResponse);

        $startDate = new Carbon();
        $endDate = new Carbon();

        $reportCollection = $this->netgsm->getReports($report, $startDate, $endDate, [
            'version' => $version
        ])->keyBy('jobId');

        foreach($reportCollection as $jobId => $item){
            $existingItem = array_filter($collection[$jobId]);
            $item = array_filter($item);
            $this->assertTrue($existingItem == $item);
        }
    }
}
