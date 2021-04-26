<?php

namespace TarfinLabs\Netgsm\Iys\Requests;

class Add
{
    protected string $url = 'iys/add';

    protected string $refId;

    protected string $type;

    protected string $source;

    protected string $recipient;

    protected string $status;

    protected string $consentDate;

    protected string $recipientType;

    protected ?int $retailerCode;

    protected ?int $retailerAccess;

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
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $source
     * @return $this
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

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
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $consentDate
     * @return $this
     */
    public function setConsentDate(string $consentDate): self
    {
        $this->consentDate = $consentDate;

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
     * @param int|null $retailerCode
     * @return $this
     */
    public function setRetailerCode(?int $retailerCode): self
    {
        $this->retailerCode = $retailerCode;

        return $this;
    }

    /**
     * @param int|null $retailerAccess
     * @return $this
     */
    public function setRetailerAccess(?int $retailerAccess): self
    {
        $this->retailerAccess = $retailerAccess;

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
            'refid'             => $this->refId ?? null,
            'type'              => $this->type ?? null,
            'source'            => $this->source ?? null,
            'recipient'         => $this->recipient ?? null,
            'status'            => $this->status ?? null,
            'consentDate'       => $this->consentDate ?? null,
            'recipientType'     => $this->recipientType ?? null,
            'retailerCode'      => $this->retailerCode ?? null,
            'retailerAccess'    => $this->retailerAccess ?? null,
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
