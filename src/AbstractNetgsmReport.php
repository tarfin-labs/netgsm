<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use TarfinLabs\Netgsm\Exceptions\ReportException;

abstract class AbstractNetgsmReport
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
    protected $credentials = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $columnMap = [];

    /**
     * @var ClientInterface
     */
    protected $client;
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
     * @param  array  $credentials
     * @return AbstractNetgsmReport
     */
    public function setCredentials(array $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @param  ClientInterface  $client
     * @return AbstractNetgsmReport
     */
    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

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
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ReportException
     */
    public function getReports(): Collection
    {
        $data = [
            'usercode' => $this->credentials['user_code'],
            'password' => $this->credentials['secret'],
            'page'     => 1,
        ];

        $data = array_merge($data, $this->filters);
        $keep = true;
        $allResults = new Collection();
        do {
            $queryStr = http_build_query($data);

            $rawResponse = $this->client->request('GET', $this->url.'?'.$queryStr)
                ->getBody()
                ->getContents();

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
