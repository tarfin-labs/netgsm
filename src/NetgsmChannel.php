<?php

namespace TarfinLabs\Netgsm;

use Illuminate\Notifications\Notification;

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
     * @param $notifiable
     * @param  Notification  $notification
     * @throws Exceptions\CouldNotSendNotification
     * @throws Exceptions\IncorrectPhoneNumberFormatException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toNetgsm($notifiable);

        if (!$message instanceof AbstractNetgsmMessage) {
            throw new \Exception('Geçerli bir Netgsm mesajı değil');
        }

        $phone = $notifiable->routeNotificationFor('Netgsm');

        $message->setRecipients($phone);

        $this->netgsm->sendSms($message);
    }
}
