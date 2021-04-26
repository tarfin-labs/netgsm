<?php

namespace TarfinLabs\Netgsm\Iys\Requests;

class Search
{
    protected string $url = 'iys/search';

    protected string $type;

    protected string $recipient;

    protected string $recipientType;

    protected string $refId;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $recipient
     * @return $this
     */
    public function setRecipient(string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @param string $recipientType
     * @return $this
     */
    public function setRecipientType(string $recipientType): self
    {
        $this->recipientType = $recipientType;

        return $this;
    }

    /**
     * @param string $refId
     * @return $this
     */
    public function setRefId(string $refId): self
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * @param array $defaults
     * @return $this
     */
    public function setDefaults(array $defaults): self
    {
        foreach ($defaults as $key => $value) {
            if (method_exists($this, 'set'. ucfirst($key))) {
                call_user_func([$this, 'set' . ucfirst($key)], $value);
            }
        }

        return $this;
    }

    /**
     * Get request body.
     *
     * @return array
     */
    public function body(): array
    {
        return [
            'type'              => $this->type ?? null,
            'recipient'         => $this->recipient ?? null,
            'recipientType'     => $this->recipientType ?? null,
            'refid'             => $this->refId ?? null,
        ];
    }

    /**
     * Get request url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
