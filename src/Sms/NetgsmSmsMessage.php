<?php

namespace TarfinLabs\Netgsm\Sms;

use TarfinLabs\Netgsm\NetgsmErrors;

class NetgsmSmsMessage extends AbstractNetgsmMessage
{
    protected $url = 'sms/send';

    protected $errorCodes = [
        '20' => NetgsmErrors::MESSAGE_TOO_LONG,
        '30' => NetgsmErrors::CREDENTIALS_INCORRECT,
        '40' => NetgsmErrors::SENDER_INCORRECT,
        '70' => NetgsmErrors::PARAMETERS_INCORRECT,
    ];

    protected $fields = [
        'gsmno',
        'message',
        'msgheader',
        'startdate',
        'stopdate',
        'dil',
        'izin',
    ];

    /**
     * creates the xml request body for sms sending via xml post method.
     *
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

    /**
     * maps the given parameters according to the required parameters of the sms message endpoint.
     *
     * @return array
     */
    protected function mappers(): array
    {
        return [
            'gsmno'     => implode(',', $this->recipients),
            'msgheader' => $this->header ?? $this->defaults['header'],
            'message'   => $this->message,
            'startdate' => ! empty($this->startDate) ? $this->startDate->format('dmYHi') : null,
            'stopdate'  => ! empty($this->endDate) ? $this->endDate->format('dmYHi') : null,
            'izin'      => (int) $this->isAuthorizedData(),
        ];
    }
}
