<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Exceptions\IncorrectPhoneNumberFormatException;
use TarfinLabs\Netgsm\Netgsm;
use TarfinLabs\Netgsm\NetgsmSmsMessage;

class NetGsmTest extends BaseTestCase
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
        ],[
            'language' => $this->faker->languageCode,
            'header' => $this->faker->word,
            'sms_sending_method' => $this->faker->randomElement(['xml','get'])
        ]);
    }

    /**
     * @param $phone
     * @return NetgsmSmsMessage
     */
    protected function newSmsMessage($phone = null)
    {
        $netgsmMessage = new NetgsmSmsMessage($this->faker->sentence($this->faker->numberBetween(1,5)));
        $netgsmMessage->setRecipients($phone ?? $this->faker->numberBetween(1111111111, 9999999999));
        return $netgsmMessage;
    }

    /**
     * @test
     */
    public function it_can_send_sms_message_with_correct_arguments()
    {
        $code = $this->faker->randomElement(['00','01','02']);

        $jobId = "98465465484";
        $response = $code.' '.$jobId;

        $this->httpClient->shouldReceive('request')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                $response
            ));

        $returnedJobId = $this->netgsm->sendSms($this->newSmsMessage());

        $this->assertSame($jobId, $returnedJobId);
    }

    /**
     * @test
     */
    public function should_throw_exception_when_return_code_is_not_success(){

        $this->expectException(CouldNotSendNotification::class);

        $this->httpClient->shouldReceive('request')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                $this->faker->randomElement(['20','30','40','70'])
            ));

        $this->netgsm->sendSms($this->newSmsMessage());
    }

    /**
     * @test
     */
    public function should_throw_exception_when_phone_number_is_incorrect(){

        $this->expectException(IncorrectPhoneNumberFormatException::class);

        $this->netgsm->sendSms($this->newSmsMessage("00000"));
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
