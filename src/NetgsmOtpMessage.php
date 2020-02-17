<?php


namespace TarfinLabs\Netgsm;


use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;

class NetgsmOtpMessage extends AbstractNetgsmMessage
{
    protected $url = 'https://api.netgsm.com.tr/sms/send/otp';

    protected $errorCodes = [
        '20'  => CouldNotSendNotification::MESSAGE_TOO_LONG,
        '30'  => CouldNotSendNotification::CREDENTIALS_INCORRECT,
        '40'  => CouldNotSendNotification::SENDER_INCORRECT,
        '50'  => CouldNotSendNotification::RECEIVER_INCORRECT,
        '60'  => CouldNotSendNotification::OTP_ACCOUNT_NOT_DEFINED,
        '70'  => CouldNotSendNotification::PARAMETERS_INCORRECT,
        '80'  => CouldNotSendNotification::QUERY_LIMIT_EXCEED,
        '100' => CouldNotSendNotification::SYSTEM_ERROR,
    ];

    protected $fields = [
        'usercode',
        'password',
        'msgheader',
        'msg',
        'no'
    ];

    /**
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
     * @return array
     */
    protected function mappers(): array
    {
        return [
            'usercode'  => $this->credentials['user_code'],
            'password'  => $this->credentials['secret'],
            'msgheader' => $this->header ?? $this->defaults['header'],
            'msg'       => $this->message,
            'no'        => is_array($this->recipients) ? $this->recipients[0] : $this->recipients,
        ];
    }
}
