<?php

namespace TarfinLabs\Netgsm\Tests;

use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Exceptions\NetgsmException;
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

    #[Test]
    public function it_can_parse_xml_response_successfully()
    {
        $message = new NetgsmSmsMessage('Test message');
        $message->setRecipients('31650520659');

        $this->setResponseAndParse($message, '<?xml version="1.0"?>
<xml><main><code>0</code><jobID>176217829127282816710591819</jobID></main></xml>');

        $this->assertEquals('176217829127282816710591819', $message->getJobId());
    }

    #[Test]
    public function it_can_parse_xml_response_with_single_digit_code()
    {
        $message = new NetgsmSmsMessage('Test message');
        $message->setRecipients('31650520659');

        $this->setResponseAndParse($message, '<?xml version="1.0"?>
<xml><main><code>1</code><jobID>123456789</jobID></main></xml>');

        $this->assertEquals('123456789', $message->getJobId());
    }

    #[Test]
    public function it_can_parse_legacy_space_separated_response()
    {
        $message = new NetgsmSmsMessage('Test message');
        $message->setRecipients('31650520659');

        $this->setResponseAndParse($message, '00 123456789');

        $this->assertEquals('123456789', $message->getJobId());
    }

    #[Test]
    public function it_throws_exception_for_invalid_xml_response_code()
    {
        $this->expectException(CouldNotSendNotification::class);

        $message = new NetgsmSmsMessage('Test message');
        $message->setRecipients('31650520659');

        $this->setResponseAndParse($message, '<?xml version="1.0"?>
<xml><main><code>30</code><jobID>123456789</jobID></main></xml>');
    }

    #[Test]
    public function it_throws_exception_for_missing_job_id_in_xml()
    {
        $this->expectException(NetgsmException::class);

        $message = new NetgsmSmsMessage('Test message');
        $message->setRecipients('31650520659');

        $this->setResponseAndParse($message, '<?xml version="1.0"?>
<xml><main><code>0</code><jobID></jobID></main></xml>');
    }

    private function setResponseAndParse(NetgsmSmsMessage $message, string $response): void
    {
        $reflection = new ReflectionClass($message);

        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($message, $response);

        $parseMethod = $reflection->getMethod('parseResponse');
        $parseMethod->setAccessible(true);
        $parseMethod->invoke($message);
    }
}
