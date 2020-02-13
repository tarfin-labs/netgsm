<?php

namespace TarfinLabs\Netgsm\Tests\notification;

use Illuminate\Notifications\Notification;

class TestStringNotification extends Notification
{
    public function toNetGsm($notifiable)
    {
        return 'Test by string';
    }
}
