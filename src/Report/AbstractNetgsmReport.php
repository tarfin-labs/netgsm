<?php

namespace TarfinLabs\Netgsm\Report;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use TarfinLabs\Netgsm\Exceptions\ReportException;
use TarfinLabs\Netgsm\NetgsmApiClient;

abstract class AbstractNetgsmReport extends NetgsmApiClient
{
    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * @var array
     */
    protected $noResultCodes = [];

    /**
     * @var bool
     */
    protected $paginated = false;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $columnMap = [];

    /**
     * @var string endpoint url
     */
    protected $url;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @param  string  $response
     * @return Collection
     */
    abstract protected function parseResponse(string $response): Collection;

    /**
     * @param  string  $type
     * @return AbstractNetgsmReport
     */
    protected function setType(string $type): self
    {
        $this->filters['type'] = $type;

        return $this;
    }

    /**
     * @param  Carbon|\Carbon\Carbon  $startDate
     * @return $this
     */
    public function setStartDate($startDate): self
    {
        $this->filters['bastar'] = $startDate->format('dmY');

        return $this;
    }

    /**
     * @param  Carbon|\Carbon\Carbon  $endDate
     * @return $this
     */
    public function setEndDate($endDate): self
    {
        $this->filters['bittar'] = $endDate->format('dmY');

        return $this;
    }

    /**
     * @param  mixed  $bulkId
     * @return AbstractNetgsmReport
     */
    public function setBulkId($bulkId): self
    {
        $this->filters['bulkid'] = is_array($bulkId) ? implode(',', $bulkId) : $bulkId;

        return $this;
    }

    /**
     * @param  string  $version
     * @return AbstractNetgsmReport
     */
    public function setVersion(string $version): self
    {
        $this->filters['version'] = $version;

        return $this;
    }

    /**
     * @param  string  $view
     * @return AbstractNetgsmReport
     */
    public function setView(string $view): self
    {
        $this->filters['view'] = $view;

        return $this;
    }

    /**
     * @param  int  $page
     * @return AbstractNetgsmReport
     */
    public function setPage(int $page): self
    {
        $this->filters['page'] = $page;

        return $this;
    }

    /**
     * formats the value by specified type.
     *
     * @param $value
     * @param $format
     * @return int|string
     */
    protected function formatValue($value, $format)
    {
        switch ($format) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'string':
                $value = ''.$value;
                break;
        }

        return $value;
    }

    /**
     * returns the netgsm basic sms reports as a collection.
     *
     * @return Collection
     * @throws GuzzleException
     * @throws ReportException
     */
    public function getReports(): Collection
    {
        $data = [
            'page'     => 1,
        ];

        $data = array_merge($data, $this->filters);
        $keep = true;
        $allResults = new Collection();
        do {
            $rawResponse = $this->callApi('GET', $this->url, $data);

            if ($this->paginated) {
                if (in_array($rawResponse, $this->noResultCodes)) {
                    $keep = false;
                } else {
                    $data['page']++;
                    $response = $this->parseResponse($rawResponse);
                    $allResults = $allResults->merge($response);
                }
            } else {
                $response = $this->parseResponse($rawResponse);
                $allResults = $allResults->merge($response);
                $keep = false;
            }
        } while ($keep);

        return $allResults;
    }

    /**
     * validates the response returned from netgsm report api.
     *
     * @param $response
     * @return bool
     * @throws ReportException
     */
    public function validateResponse($response): bool
    {
        if (in_array(intval($response), $this->errorCodes)) {
            throw new ReportException('Netgsm report error', $response);
        }

        if (in_array($response, $this->noResultCodes)) {
            return false;
        }

        return true;
    }
}
