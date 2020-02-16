<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use SimpleXMLElement;
use TarfinLabs\Netgsm\Exceptions\ReportException;
use TarfinLabs\Netgsm\Netgsm;
use TarfinLabs\Netgsm\NetgsmSmsDetailReport;

class NetGsmReportDetailTest extends BaseTestCase
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
     * @param $rowCount
     * @param $times
     * @return array
     */
    protected function mockReportApiRequest($rowCount, $times)
    {
        $generatedData = [];
        $responses = [];
        for ($i = 0; $i < $times; $i++) {
            $datum = $this->generateResponse($rowCount);
            $generatedData = array_merge($generatedData, $datum);
            $responses[] = new Response(
                $status = 200,
                $headers = [],
                $this->convertXml($datum)
            );
        }

        $lastResponse = new Response(
            $status = 200,
            $headers = [],
            "100"
        );

        $responses[] = $lastResponse;

        $this->httpClient->shouldReceive('request')
            ->withAnyArgs()
            ->andReturnValues($responses);

        return $generatedData;
    }

    /**
     * @param $rowCount
     * @return array
     */
    protected function generateResponse($rowCount): array
    {
        $response = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $response[] = [
                'jobId'     => $this->faker->numberBetween(11111, 99999),
                'startDate' => $this->faker->date,
                'endDate'   => $this->faker->date,
                'header'    => $this->faker->word,
                'message'   => $this->faker->sentence,
                'status'    => $this->faker->randomElement(range(1, 9)),
                'total'     => $this->faker->numberBetween(1, 10)
            ];
        }
        return $response;
    }

    /**
     * @param $items
     * @return string|null
     */
    protected function convertXml($items)
    {
        $xml = new SimpleXMLElement('<mainbody/>');
        $child = $xml->addChild('header');

        foreach ($items as $item) {

            $report = $child->addChild('SMSReport');

            $datetimeChild = $report->addChild('datetime');
            $datetimeChild->addChild('startdate', $item['startDate']);
            $datetimeChild->addChild('stopdate', $item['endDate']);

            $msgInfoChild = $report->addChild('msginfo');
            $msgInfoChild->addChild('jobID', $item['jobId']);
            $msgInfoChild->addChild('msgheader', $item['header']);
            $msgInfoChild->addChild('groups', "--");
            $msgInfoChild->addChild('msg', $item['message']);
            $msgInfoChild->addChild('state', $item['status']);
            $msgInfoChild->addChild('total', $item['total']);
        }

        return $xml->asXML();
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
     * @param  int  $page
     * @return NetgsmSmsDetailReport
     */
    protected function newSmsReport($page = 1)
    {
        $report = new NetgsmSmsDetailReport();
        $report->setPage($page);

        return $report;
    }

    /**
     * @test
     */
    public function it_does_get_same_row_count_with_correct_arguments()
    {
        $type = $this->faker->randomElement([0, 1]);
        $rowCount = $this->faker->numberBetween(15, 30);
        $pages = $this->faker->numberBetween(5, 10);
        $report = $this->newSmsReport();
        $this->mockReportApiRequest($rowCount, $pages);

        $filters = [
            'type' => $type
        ];

        if ($type == 0) {
            $filters['bulkid'] = $this->faker->numberBetween(11111, 99999);
        }

        $startDate = new Carbon();
        $endDate = new Carbon();

        $report = $this->netgsm->getReports($report, $startDate, $endDate);

        $this->assertSame($report->count(), $rowCount * $pages);
    }

    /**
     * @test
     */
    public function should_throw_exception_when_return_code_is_not_success()
    {
        $version = $this->faker->randomElement([0, 1, 2, 3]);
        $report = $this->newSmsReport();
        $errorCode = $this->faker->randomElement([30, 60, 70]);

        $this->httpClient->shouldReceive('request')
            ->withAnyArgs()
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                $errorCode
            ));

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
        $pages = $this->faker->numberBetween(5, 10);
        $report = $this->newSmsReport();
        $generatedData = $this->mockReportApiRequest($rowCount, $pages);
        $collection = collect($generatedData)->keyBy('jobId');

        $startDate = new Carbon();
        $endDate = new Carbon();

        $reportCollection = $this->netgsm->getReports($report, $startDate, $endDate, [
            'version' => $version
        ])->keyBy('jobId');

        foreach ($reportCollection as $jobId => $item) {
            $existingItem = array_filter($collection[$jobId]);
            $item = array_filter($item);
            $this->assertTrue($existingItem == $item);
        }
    }
}
