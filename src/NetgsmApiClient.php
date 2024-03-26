<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class NetgsmApiClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $credentials = [];

    /**
     * @param  mixed  $client
     * @return self
     */
    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param  array  $credentials
     * @return $this
     */
    public function setCredentials(array $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Sends requests to netgsm api endpoints with specified credentials.
     *
     * @param  $method
     * @param  $url
     * @param  null  $params
     * @param  array  $headers
     * @return string
     *
     * @throws GuzzleException
     */
    protected function callApi($method, $url, $params = null, $headers = [])
    {
        $options = [
            'query' => [
                'usercode' => $this->credentials['user_code'],
                'password' => $this->credentials['secret'],
            ],
        ];

        if ($method == 'POST') {
            if (is_array($params)) {
                $options['form_params'] = $params;
            } else {
                $options['body'] = $params;
            }
        }

        if ($method == 'GET' && is_array($params)) {
            $options['query'] = array_merge($options['query'], $params);
        }

        if ($headers) {
            $options['headers'] = $headers;
        }

        $response = $this->client->request($method, $url, $options);

        return $response->getBody()->getContents();
    }
}
