<?php

namespace TarfinLabs\Netgsm\Sms;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Psr\Http\Message\ResponseInterface;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Exceptions\IncorrectPhoneNumberFormatException;
use TarfinLabs\Netgsm\NetgsmApiClient;
use TarfinLabs\Netgsm\NetgsmErrors;

abstract class AbstractNetgsmMessage extends NetgsmApiClient
{
    private const SUCCESS_CODES = [
        '00', '01', '02',
    ];

    protected $sendMethods = ['xml', 'get'];

    /**
     * @var string
     */
    protected $sendMethod;

    /**
     * @var string[]
     */
    protected $recipients = [];

    /**
     * @var null
     */
    protected $header = null;

    /**
     * @var Carbon
     */
    protected $startDate;

    /**
     * @var Carbon
     */
    protected $endDate;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var string message
     */
    protected $message;

    /**
     * authorized data parameter.
     *
     * @see https://www.netgsm.com.tr/dokuman/#http-get-sms-g%C3%B6nderme
     * @see https://www.netgsm.com.tr/dokuman/#xml-post-sms-g%C3%B6nderme
     *
     * @var bool
     */
    protected $authorizedData = false;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $errorCodes;

    /**
     * @param  string  $message
     * @param  array  $defaults
     * @return static
     */
    public static function create(string $message = null, array $defaults = [])
    {
        return new static($message, $defaults);
    }

    /**
     * AbstractNetgsmMessage constructor.
     * @param  array  $defaults
     * @param  string  $message
     */
    public function __construct(string $message = null, array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->message = $message;
    }

    /**
     * @return array
     */
    abstract protected function mappers(): array;

    /**
     * @return string
     */
    abstract protected function createXmlPost(): string;

    /**
     * @param  string|array|$recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        if (! is_array($recipients)) {
            $this->recipients = explode(',', $recipients);
        } else {
            $this->recipients = $recipients;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param  null  $header
     * @return AbstractNetgsmMessage
     */
    public function setHeader($header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header ?? $this->defaults['header'];
    }

    /**
     * @param  string  $message
     * @return AbstractNetgsmMessage
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getSendMethod(): string
    {
        return $this->sendMethod ?? $this->defaults['sms_sending_method'];
    }

    /**
     * @param  string  $sendMethod
     * @return $this
     * @throws Exception
     */
    public function setSendMethod(string $sendMethod): self
    {
        if (! in_array($sendMethod, $this->sendMethods)) {
            throw new Exception($sendMethod.' method is not allowed');
        }

        $this->sendMethod = $sendMethod;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorizedData(): bool
    {
        return $this->authorizedData;
    }

    /**
     * @param  bool  $authorizedData
     * @return AbstractNetgsmMessage
     */
    public function setAuthorizedData(bool $authorizedData): self
    {
        $this->authorizedData = $authorizedData;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url.'/'.$this->getSendMethod();
    }

    /**
     * @throws IncorrectPhoneNumberFormatException
     */
    protected function validateRecipients(): void
    {
        if (count($this->recipients) == 0) {
            throw new IncorrectPhoneNumberFormatException();
        }
        foreach ($this->recipients as $recipient) {
            if (strstr($recipient, ' ') || strlen($recipient) < 10) {
                throw new IncorrectPhoneNumberFormatException();
            }
        }
    }

    /**
     * @return string
     */
    public function body(): array
    {
        return array_merge(array_flip($this->fields), array_filter($this->mappers()));
    }

    /**
     * @param  array  $defaults
     * @return AbstractNetgsmMessage
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @param  Carbon  $startDate
     * @return AbstractNetgsmMessage
     */
    public function setStartDate(Carbon $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @param  Carbon  $endDate
     * @return AbstractNetgsmMessage
     */
    public function setEndDate(Carbon $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     */
    public function parseResponse(): self
    {
        $result = explode(' ', $this->response);

        if (! isset($result[0])) {
            throw new CouldNotSendNotification(NetgsmErrors::NETGSM_GENERAL_ERROR);
        }

        if (! in_array($result[0], self::SUCCESS_CODES)) {
            $message = $this->errorCodes[$result[0]];
            throw new CouldNotSendNotification($message);
        }

        $this->code = $result[0];
        $this->jobId = $result[1];

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     * @throws GuzzleException
     */
    protected function sendViaGet(): self
    {
        $this->response = $this->callApi('GET', $this->getUrl(), $this->body());

        return $this->parseResponse();
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     * @throws GuzzleException
     */
    protected function sendViaXml(): self
    {
        $this->response = $this->callApi('POST', $this->getUrl(), $this->createXmlPost(), [
            'Content-Type' => 'text/xml; charset=UTF8',
        ]);

        return $this->parseResponse();
    }

    /**
     * @return $this
     * @throws IncorrectPhoneNumberFormatException
     */
    public function send()
    {
        $this->validateRecipients();
        $method = 'sendVia'.$this->getSendMethod();

        return call_user_func([$this, $method]);
    }
}
