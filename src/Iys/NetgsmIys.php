<?php

namespace TarfinLabs\Netgsm\Iys;

use TarfinLabs\Netgsm\Iys\Requests\Add;
use TarfinLabs\Netgsm\Iys\Requests\Search;

class NetgsmIys extends AbstractNetgsmIys
{
    /**
     * Add address request.
     *
     * @param Add $request
     * @return $this
     */
    public function addAddress(Add $request): NetgsmIys
    {
        $this->url = $request->getUrl();
        $this->body[] = $request->body();
        $this->method = 'POST';

        return $this;
    }

    /**
     * Search address request.
     *
     * @param Search $request
     * @return $this
     */
    public function searchAddress(Search $request): NetgsmIys
    {
        $this->url = $request->getUrl();
        $this->body = [$request->body()];
        $this->method = 'POST';

        return $this;
    }
}
