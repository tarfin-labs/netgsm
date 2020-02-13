<?php

namespace TarfinLabs\Netgsm\Tests\notification;

use Illuminate\Notifications\Notifiable;

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForNetgsm()
    {
        return '31650520659';
    }
}
