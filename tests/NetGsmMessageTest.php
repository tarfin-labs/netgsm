<?php

namespace TarfinLabs\Netgsm\Tests;

use PHPUnit\Framework\Attributes\Test;
use TarfinLabs\Netgsm\Sms\NetgsmSmsMessage;

class NetGsmMessageTest extends BaseTestCase
{
    #[Test]
    public function it_supports_create_method()
    {
        $content = $this->faker->sentence;

        $message = NetgsmSmsMessage::create($content);

        $this->assertInstanceOf(NetgsmSmsMessage::class, $message);
        $this->assertEquals($content, $message->getMessage());
    }

    #[Test]
    public function it_can_set_body()
    {
        $content = $this->faker->sentence;

        $message = (new NetgsmSmsMessage)->setMessage($content);

        $this->assertEquals($content, $message->getMessage());
    }

    #[Test]
    public function it_can_set_header()
    {
        $name = $this->faker->company;

        $message = (new NetgsmSmsMessage)->setHeader($name);

        $this->assertEquals($name, $message->getHeader());
    }

    #[Test]
    public function it_can_set_send_method()
    {
        $method = $this->faker->randomElement(['xml', 'get']);

        $message = (new NetgsmSmsMessage)->setSendMethod($method);

        $this->assertEquals($method, $message->getSendMethod());
    }

    #[Test]
    public function it_can_set_needs_authorized_data()
    {
        $authorizedData = $this->faker->boolean;

        $message = (new NetgsmSmsMessage)->setAuthorizedData($authorizedData);

        $this->assertEquals($authorizedData, $message->isAuthorizedData());
    }

    #[Test]
    public function it_can_set_recipients_from_array()
    {
        $message = (new NetgsmSmsMessage)->setRecipients([31650520659, 31599858770]);

        $this->assertEquals(['31650520659', '31599858770'], $message->getRecipients());
    }

    #[Test]
    public function it_can_set_recipients_from_integer()
    {
        $message = (new NetgsmSmsMessage)->setRecipients(31650520659);

        $this->assertEquals([31650520659], $message->getRecipients());
    }

    #[Test]
    public function it_can_set_recipients_from_string()
    {
        $message = (new NetgsmSmsMessage)->setRecipients('31650520659');

        $this->assertEquals(['31650520659'], $message->getRecipients());
    }
}
