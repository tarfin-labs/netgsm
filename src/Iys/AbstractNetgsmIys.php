<?php

namespace TarfinLabs\Netgsm\Iys;

use TarfinLabs\Netgsm\NetgsmApiClient;

abstract class AbstractNetgsmIys extends NetgsmApiClient
{
    protected string $url = '';

    protected string $method = 'GET';

    protected array $body = [];

    /**
     * Send request.
     *
     * @return string
     */
    public function send(): string
    {
        $response = $this->client->request($this->method, $this->url, [
            'headers'       => [
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'header'    => [
                    'username'  => $this->credentials['user_code'],
                    'password'  => $this->credentials['secret'],
                    'brandCode' => $this->credentials['brand_code'],
                ],
                'body'      => [
                    'data'      => [
                        $this->body
                    ],
                ],
            ],
        ]);

        return $response->getBody()->getContents();
    }
}
