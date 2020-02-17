<?php

namespace TarfinLabs\Netgsm\Tests;

use GuzzleHttp\Client;
use Mockery;
use TarfinLabs\Netgsm\Netgsm;
use TarfinLabs\Netgsm\NetgsmChannel;
use TarfinLabs\Netgsm\Tests\notification\TestNotifiable;
use TarfinLabs\Netgsm\Tests\notification\TestNotification;
use TarfinLabs\Netgsm\Tests\notification\TestStringNotification;

class NetGsmChannelTest extends BaseTestCase
{
    /** @var Netgsm */
    protected $netgsm;

    /** @var NetgsmChannel */
    protected $channel;

    /** @var TestNotification */
    protected $notification;

    /** @var TestNotifiable */
    protected $notifiable;

    /** @var TestStringNotification */
    protected $stringNotification;

    /**
     * @var Client
     */
    protected $httpClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(new Client());

        $this->notification = new TestNotification;
        $this->stringNotification = new TestStringNotification;
        $this->notifiable = new TestNotifiable;

        $this->netgsm = Mockery::mock(new Netgsm($this->httpClient, [
            'userCode' => '',
            'secret' => '',
        ]));

        $this->channel = new NetGsmChannel($this->netgsm);
    }

    /** @test */
    public function test_it_shares_message(): void
    {
        $this->netgsm->shouldReceive('sendSms')->once();
        $this->channel->send($this->notifiable, $this->notification);

        $this->assertEquals(1, $this->netgsm->mockery_getExpectationCount());
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
