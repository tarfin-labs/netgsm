<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use TarfinLabs\Netgsm\Exceptions\NetgsmException;
use TarfinLabs\Netgsm\Netgsm;

class NetgsmAvailableCreditTest extends BaseTestCase
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
            'secret'    => $this->faker->password,
        ]);
    }

    /**
     * @param $response
     */
    protected function mockCreditApiRequest($response)
    {
        $this->httpClient->shouldReceive('request')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                $response
            ));
    }

    /**
     * @test
     */
    public function it_get_available_credit_balance_with_correct_arguments()
    {
        $credit = $this->faker->numberBetween(5, 10);

        $response = '00 '.$credit;

        $this->mockCreditApiRequest($response);

        $response = $this->netgsm->getCredit();

        $this->assertSame((string) $credit, $response);
    }

    /**
     * @test
     */
    public function should_throw_exception_when_return_code_is_not_success()
    {
        $errorCode = $this->faker->randomElement([30, 40, 100]);
        $this->mockCreditApiRequest($errorCode);

        $this->expectException(NetgsmException::class);
        $this->expectExceptionCode($errorCode);

        $this->netgsm->getCredit();
    }
}
