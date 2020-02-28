<?php

namespace TarfinLabs\Netgsm\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;

class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @param  ClientException  $exception
     *
     * @return static
     */
    public static function NetgsmRespondedWithAnError(ClientException $exception)
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $description = 'no description given';
        if ($result = json_decode($exception->getResponse()->getBody())) {
            $description = $result->description ?: $description;
        }

        return new static("Netgsm responded with an error `{$statusCode} - {$description}`");
    }
}
