<?php

namespace TarfinLabs\Netgsm\Tests\notification;


use Illuminate\Notifications\Notification;
use TarfinLabs\Netgsm\NetgsmSmsMessage;

class TestNotification extends Notification
{
    public function toNetgsm($notifiable)
    {
        return (new NetgsmSmsMessage('Message content'))
            ->setHeader('COMPANY')
            ->setRecipients('31650520659');
    }
}
