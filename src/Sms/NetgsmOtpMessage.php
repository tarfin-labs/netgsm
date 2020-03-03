<?php

namespace TarfinLabs\Netgsm\Sms;

use TarfinLabs\Netgsm\NetgsmErrors;

class NetgsmOtpMessage extends AbstractNetgsmMessage
{
    protected $url = 'sms/send/otp';

    protected $errorCodes = [
        '20'  => NetgsmErrors::MESSAGE_TOO_LONG,
        '30'  => NetgsmErrors::CREDENTIALS_INCORRECT,
        '40'  => NetgsmErrors::SENDER_INCORRECT,
        '50'  => NetgsmErrors::RECEIVER_INCORRECT,
        '60'  => NetgsmErrors::OTP_ACCOUNT_NOT_DEFINED,
        '70'  => NetgsmErrors::PARAMETERS_INCORRECT,
        '80'  => NetgsmErrors::QUERY_LIMIT_EXCEED,
        '100' => NetgsmErrors::SYSTEM_ERROR,
    ];

    protected $fields = [
        'msgheader',
        'msg',
        'no',
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
        $xml .= '<usercode>'.$this->credentials['user_code'].'</usercode>';
        $xml .= '<password>'.$this->credentials['secret'].'</password>';
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
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * maps the given parameters according to the required parameters of the otp message endpoint.
     *
     * @return array
     */
    protected function mappers(): array
    {
        return [
            'msgheader' => $this->header ?? $this->defaults['header'],
            'msg'       => $this->message,
            'no'        => is_array($this->recipients) ? $this->recipients[0] : $this->recipients,
        ];
    }
}
