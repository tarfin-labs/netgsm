<?php

namespace TarfinLabs\Netgsm\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    /**
     * @return static
     */
    public static function configurationNotSet()
    {
        return new static('In order to send notification via netgsm you need to add credentials in the `netgsm` key of `config.services`.');
    }
}
