<?php

namespace TarfinLabs\Netgsm\Balance;

use GuzzleHttp\Exception\GuzzleException;
use TarfinLabs\Netgsm\Exceptions\NetgsmException;
use TarfinLabs\Netgsm\NetgsmApiClient;
use TarfinLabs\Netgsm\NetgsmErrors;

class NetgsmPackages extends NetgsmApiClient
{
    /**
     * @var string
     */
    protected $response;

    protected $url = 'balance/list/get';

    /**
     * @var array
     */
    protected $errorCodes = [
        '30'  => NetgsmErrors::MESSAGE_TOO_LONG,
        '40'  => NetgsmErrors::CREDENTIALS_INCORRECT,
        '100' => NetgsmErrors::SYSTEM_ERROR,
    ];


    /**
     * @return array
     * @throws NetgsmException
     */
    public function parseResponse(): array
    {
        $availablePackages = [];

        if (array_key_exists($this->response, $this->errorCodes)) {
            $message = $this->errorCodes[$this->response];
            throw new NetgsmException($message, $this->response);
        }

        $rows = array_filter(explode('<BR>', $this->response));
        foreach ($rows as $row) {
            $columns = array_filter(explode('|', $row));
            $columns = array_map('trim', $columns);
            $availablePackages[] = [
                'amount'      => (int) $columns[0],
                'amountType'  => $columns[1] ?? null,
                'packageType' => $columns[2] ?? null
            ];
        }

        return $availablePackages;
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws NetgsmException
     */
    public function getPackages(): array
    {
        $this->response = $this->callApi('GET', $this->url, ['tip' => 1]);

        return $this->parseResponse();
    }
}
