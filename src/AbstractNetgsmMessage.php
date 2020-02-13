<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\Carbon;
use Psr\Http\Message\ResponseInterface;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Exceptions\IncorrectPhoneNumberFormatException;

abstract class AbstractNetgsmMessage
{
    const SUCCESS_CODES = [
        '00', '01', '02',
    ];

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
     * @param  string|array|$recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        if (!is_array($recipients)) {
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
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return null
     */
    public function getHeader()
    {
        return $this->header;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @throws IncorrectPhoneNumberFormatException
     */
    protected function validateRecipients()
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
    public function body(): string
    {
        $params = array_merge(array_flip($this->fields), array_filter($this->mappers()));

        return http_build_query($params);
    }

    /**
     * @param  mixed  $client
     * @return AbstractNetgsmMessage
     */
    public function setClient(ClientInterface $client)
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
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @return $this
     * @throws CouldNotSendNotification
     */
    public function parseResponse()
    {
        $result = explode(' ', $this->getResponseContent());

        if (!isset($result[0])) {
            throw new CouldNotSendNotification(CouldNotSendNotification::NETGSM_GENERAL_ERROR);
        }

        if (!in_array($result[0], self::SUCCESS_CODES)) {
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
    public function send()
    {
        $this->validateRecipients();

        $this->response = $this->client->request('GET', $this->url.'?'.$this->body());

        return $this->parseResponse();
    }
}
