<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
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
            'user_code'  => 'test',
            'secret'     => 'test',
            'brand_code' => 123456,
        ]);
    }

    /** @test */
    public function it_makes_add_requests_for_iys_addresses()
    {
        $data = [
            'headers' => [
                'username'      => 'test',
                'password'      => 'test',
                'brandCode'     => 123456,
            ],
            'json'    => [
                'refid'         => $this->faker->numerify('####'),
                'type'          => $this->faker->randomElement(['MESAJ', 'ARAMA']),
                'source'        => $this->faker->randomElement([
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
                'recipient'     => $this->faker->numerify('+905#########'),
                'status'        => $this->faker->randomElement(['ONAY', 'RET']),
                'consentDate'   => $this->faker->date('Y-m-d H:i:s'),
                'recipientType' => $this->faker->randomElement(['BIREYSEL', 'TACIR']),
                'retailerCode'  => null,
                'retailerAccess'=> null,
            ],
        ];

        $iysAddress = new Add();
        $iysAddress->setDefaults($data['json']);

        $this->httpClient
            ->shouldReceive('request')
            ->withSomeOfArgs('POST', 'iys/add', $data)
            ->once()
            ->andReturn(new Response());

        $this->netgsm->iys()->addAddress($iysAddress)->send();
    }

    /** @test */
    public function it_makes_search_requests_for_iys_addresses()
    {
        $data = [
            'headers' => [
                'username'      => 'test',
                'password'      => 'test',
                'brandCode'     => 123456,
            ],
            'json'    => [
                'type'          => $this->faker->randomElement(['MESAJ', 'ARAMA']),
                'recipient'     => $this->faker->numerify('+905#########'),
                'recipientType' => $this->faker->randomElement(['BIREYSEL', 'TACIR']),
                'refid'         => $this->faker->numerify('####'),
            ],
        ];

        $iysSearch = new Search();
        $iysSearch->setDefaults($data['json']);

        $this->httpClient
            ->shouldReceive('request')
            ->withSomeOfArgs('POST', 'iys/search', $data)
            ->once()
            ->andReturn(new Response());

        $this->netgsm->iys()->searchAddress($iysSearch)->send();
    }
}
