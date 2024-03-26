<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use TarfinLabs\Netgsm\Exceptions\NetgsmException;
use TarfinLabs\Netgsm\Netgsm;

class NetgsmAvailablePackagesTest extends BaseTestCase
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
            'secret' => $this->faker->password,
        ]);
    }

    /**
     * @param  $response
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
    public function it_get_available_package_amounts_with_correct_arguments()
    {
        $response = '1000 | Adet Flash Sms | <BR>953 | Adet OTP Sms | <BR>643 | Adet | SMS<BR>';

        $this->mockCreditApiRequest($response);

        $response = $this->netgsm->getAvailablePackages();

        $this->assertSame($response[0]['amount'], 1000);
        $this->assertSame($response[0]['amountType'], 'Adet Flash Sms');
        $this->assertSame($response[1]['amount'], 953);
        $this->assertSame($response[1]['amountType'], 'Adet OTP Sms');
        $this->assertSame($response[2]['amount'], 643);
        $this->assertSame($response[2]['amountType'], 'Adet');
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

        $this->netgsm->getAvailablePackages();
    }
}
