<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TarfinLabs\Netgsm\Iys\Requests\Add;
use TarfinLabs\Netgsm\Iys\Requests\Search;
use TarfinLabs\Netgsm\Netgsm;

class NetGsmIysTest extends BaseTestCase
{
    protected Netgsm $netgsm;

    protected Client $httpClient;

    protected ResponseInterface $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(new Client());
        $this->response = Mockery::mock(ResponseInterface::class);

        $this->netgsm = new Netgsm($this->httpClient, [
            'user_code' => 'test',
            'secret' => 'test',
            'brand_code' => 123456,
        ]);
    }

    #[Test]
    public function it_makes_add_requests_for_iys_addresses()
    {
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'header' => [
                    'username' => 'test',
                    'password' => 'test',
                    'brandCode' => 123456,
                ],
                'body' => [
                    'data' => [
                        [
                            'refid' => $this->faker->numerify('####'),
                            'type' => $this->faker->randomElement(['MESAJ', 'ARAMA']),
                            'source' => $this->faker->randomElement([
                                'HS_WEB',
                                'HS_FIZIKSEL_ORTAM',
                                'HS_ISLAK_IMZA',
                                'HS_CAGRI_MERKEZI',
                                'HS_SOSYAL_MEDYA',
                                'HS_EPOSTA',
                                'HS_MESAJ',
                                'HS_MOBIL',
                                'HS_EORTAM',
                                'HS_ETKINLIK',
                                'HS_2015',
                                'HS_ATM',
                                'HS_KARAR',
                            ]),
                            'recipient' => $this->faker->numerify('+905#########'),
                            'status' => $this->faker->randomElement(['ONAY', 'RET']),
                            'consentDate' => $this->faker->date('Y-m-d H:i:s'),
                            'recipientType' => $this->faker->randomElement(['BIREYSEL', 'TACIR']),
                            'retailerCode' => null,
                            'retailerAccess' => null,
                        ],
                    ],
                ],
            ],
        ];

        $iysAddress = new Add();
        $iysAddress->setDefaults($data['json']['body']['data'][0]);

        $successResponse = [
            'code' => '0',
            'error' => 'false',
            'uid' => '73113cb9-dff0-415b-9491-xxxxxxxxxx'
        ];

        $this->httpClient
            ->shouldReceive('request')
            ->withSomeOfArgs('POST', 'iys/add', $data)
            ->once()
            ->andReturn(new Response(200, [], json_encode($successResponse)));

        $response = $this->netgsm->iys()->addAddress($iysAddress)->send();
        $decodedResponse = json_decode($response, true);

        $this->assertEquals('0', $decodedResponse['code']);
        $this->assertEquals('false', $decodedResponse['error']);
        $this->assertEquals('73113cb9-dff0-415b-9491-xxxxxxxxxx', $decodedResponse['uid']);
    }

    #[Test]
    public function it_makes_search_requests_for_iys_addresses()
    {
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'header' => [
                    'username' => 'test',
                    'password' => 'test',
                    'brandCode' => 123456,
                ],
                'body' => [
                    'data' => [
                        [
                            'type' => $this->faker->randomElement(['MESAJ', 'ARAMA']),
                            'recipient' => $this->faker->numerify('+905#########'),
                            'recipientType' => $this->faker->randomElement(['BIREYSEL', 'TACIR']),
                            'refid' => $this->faker->numerify('####'),
                        ],
                    ],
                ],
            ],
        ];

        $iysSearch = new Search();
        $iysSearch->setDefaults($data['json']['body']['data'][0]);

        $searchResponse = [
            'code' => '0',
            'error' => 'false',
            'query' => [
                'consentDate' => '2020-11-06 11:22:34',
                'source' => 'HS_FIZIKSEL_ORTAM',
                'recipient' => '+905XXXXXXXXX',
                'recipientType' => 'BIREYSEL',
                'type' => 'MESAJ',
                'status' => 'ONAY',
                'creationDate' => '2020-11-06 11:23:49',
                'retailerAccessCount' => 0
            ]
        ];

        $this->httpClient
            ->shouldReceive('request')
            ->withSomeOfArgs('POST', 'iys/search', $data)
            ->once()
            ->andReturn(new Response(200, [], json_encode($searchResponse)));

        $response = $this->netgsm->iys()->searchAddress($iysSearch)->send();
        $decodedResponse = json_decode($response, true);

        $this->assertEquals('0', $decodedResponse['code']);
        $this->assertEquals('false', $decodedResponse['error']);
        $this->assertArrayHasKey('query', $decodedResponse);
        $this->assertEquals('ONAY', $decodedResponse['query']['status']);
        $this->assertEquals('MESAJ', $decodedResponse['query']['type']);
        $this->assertEquals('HS_FIZIKSEL_ORTAM', $decodedResponse['query']['source']);
        $this->assertEquals('BIREYSEL', $decodedResponse['query']['recipientType']);
        $this->assertEquals('+905XXXXXXXXX', $decodedResponse['query']['recipient']);
        $this->assertEquals('2020-11-06 11:22:34', $decodedResponse['query']['consentDate']);
        $this->assertEquals('2020-11-06 11:23:49', $decodedResponse['query']['creationDate']);
        $this->assertEquals(0, $decodedResponse['query']['retailerAccessCount']);
    }
}
