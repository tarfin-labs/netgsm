<?php

namespace TarfinLabs\Netgsm;

use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;

class NetgsmSmsMessage extends AbstractNetgsmMessage
{
    protected $url = 'https://api.netgsm.com.tr/sms/send';

    protected $errorCodes = [
        '20' => CouldNotSendNotification::MESSAGE_TOO_LONG,
        '30' => CouldNotSendNotification::CREDENTIALS_INCORRECT,
        '40' => CouldNotSendNotification::SENDER_INCORRECT,
        '70' => CouldNotSendNotification::PARAMETERS_INCORRECT,
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
        'izin',
    ];

    /**
     * @return string
     */
    protected function createXmlPost(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<mainbody>';
        $xml .= '<header>';
        $xml .= '<company dil="'.$this->defaults['language'].'">Netgsm</company>';
        $xml .= '<usercode>'.$this->credentials['user_code'].'</usercode>';
        $xml .= '<password>'.$this->credentials['secret'].'</password>';
        $xml .= '<type>1:n</type>';
        $xml .= '<filtre>'.(int) $this->isAuthorizedData().'</filtre>';
        $xml .= '<msgheader>'.$this->getHeader().'</msgheader>';
        $xml .= '</header>';
        $xml .= '<body>';
        $xml .= '<msg>';
        $xml .= '<![CDATA['.$this->message.']]>';
        $xml .= '</msg>';
        foreach ($this->recipients as $recipient) {
            $xml .= '<no>'.$recipient.'</no>';
        }
        $xml .= '</body>';
        $xml .= '</mainbody>';

        return $xml;
    }

    protected function mappers(): array
    {
        return [
            'gsmno'     => implode(',', $this->recipients),
            'msgheader' => $this->header ?? $this->defaults['header'],
            'usercode'  => $this->credentials['user_code'],
            'password'  => $this->credentials['secret'],
            'message'   => $this->message,
            'startdate' => ! empty($this->startDate) ? $this->startDate->format('dmYHi') : null,
            'stopdate'  => ! empty($this->endDate) ? $this->endDate->format('dmYHi') : null,
            'izin'      => (int) $this->isAuthorizedData(),
        ];
    }
}
