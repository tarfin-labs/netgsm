<?php

namespace TarfinLabs\Netgsm;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Notifications\Notification;
use TarfinLabs\Netgsm\Exceptions\IncorrectPhoneNumberFormatException;
use TarfinLabs\Netgsm\Sms\AbstractNetgsmMessage;
use TarfinLabs\Netgsm\Sms\NetgsmSmsMessage;

class NetgsmChannel
{
    protected $netgsm;

    public function __construct(Netgsm $netgsm)
    {
        $this->netgsm = $netgsm;
    }

    /**
     * Send the given notification.
     *
     * @param              $notifiable
     * @param Notification $notification
     * @throws Exceptions\CouldNotSendNotification
     * @throws GuzzleException
     * @throws IncorrectPhoneNumberFormatException
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toNetgsm($notifiable);

        if (! $message instanceof AbstractNetgsmMessage) {
            throw new Exception('Geçerli bir Netgsm mesajı değil');
        }

        if (! $message->getRecipients()) {
            $phone = $notifiable->routeNotificationFor('Netgsm');
            $message->setRecipients($phone);
        }

        $this->netgsm->sendSms($message);
    }
}
