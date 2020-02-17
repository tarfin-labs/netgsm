<?php

namespace TarfinLabs\Netgsm;

use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Carbon;
use Psr\Http\Message\ResponseInterface;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Exceptions\IncorrectPhoneNumberFormatException;

abstract class AbstractNetgsmMessage
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
     * @var array
     */
    protected $credentials = [];
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var string endpoint url
     */
    protected $url;
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
     * @param  mixed  $client
     * @return AbstractNetgsmMessage
     */
    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
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
     * @param  array  $credentials
     * @return AbstractNetgsmMessage
     */
    public function setCredentials(array $credentials): self
    {
        $this->credentials = $credentials;

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
     * @return string
     */
    protected function getResponseContent(): string
    {
        return $this->response->getBody()->getContents();
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
        $result = explode(' ', $this->getResponseContent());

        if (! isset($result[0])) {
            throw new CouldNotSendNotification(CouldNotSendNotification::NETGSM_GENERAL_ERROR);
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
     * @throws IncorrectPhoneNumberFormatException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendViaGet(): self
    {
        $query = http_build_query($this->body());

        $this->response = $this->client->request('GET', $this->getUrl().'?'.$query);

        return $this->parseResponse();
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendViaXml(): self
    {
        $options = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8',
            ],
            'body'    => $this->createXmlPost(),
        ];

        $this->response = $this->client->request('POST', $this->getUrl(), $options);

        return $this->parseResponse();
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     * @throws IncorrectPhoneNumberFormatException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send()
    {
        $this->validateRecipients();
        $method = 'sendVia'.$this->getSendMethod();

        return call_user_func([$this, $method]);
    }
}
