<?php

namespace TarfinLabs\Netgsm;

use Illuminate\Support\Facades\Facade;

class NetgsmFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Netgsm::class;
    }
}
