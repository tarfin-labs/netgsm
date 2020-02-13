<?php

namespace TarfinLabs\Netgsm;

use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;

class NetgsmSmsMessage extends AbstractNetgsmMessage
{
    protected $url = 'https://api.netgsm.com.tr/sms/send/get/';

    protected $errorCodes = [
        '20' => CouldNotSendNotification::MESSAGE_TOO_LONG,
        '30' => CouldNotSendNotification::CREDENTIALS_INCORRECT,
        '40' => CouldNotSendNotification::SENDER_INCORRECT,
        '70' => CouldNotSendNotification::PARAMETERS_INCORRECT
    ];

    protected $fields = [
        'usercode',
        'password',
        'gsmno',
        'message',
        'msgheader',
        'startdate',
        'stopdate',
        'dil',
        'izin'
    ];

    protected function mappers(): array
    {
        return [
            'gsmno'     => implode(',', $this->recipients),
            'msgheader' => $this->header ?? $this->defaults['sender'],
            'usercode'  => $this->credentials['user_code'],
            'password'  => $this->credentials['secret'],
            'message'   => $this->message,
            'startdate' => !empty($this->startDate) ? $this->startDate->format('dmYHi') : null,
            'stopdate'  => !empty($this->endDate) ? $this->endDate->format('dmYHi') : null
        ];
    }


}
