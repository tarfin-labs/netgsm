<?php

namespace TarfinLabs\Netgsm\Report;

use Illuminate\Support\Collection;
use SimpleXMLElement;

class NetgsmSmsDetailReport extends AbstractNetgsmReport
{
    /**
     * @var string
     */
    protected $url = 'sms/report/detail';

    /**
     * @var array
     */
    protected $errorCodes = [
        30, 60, 65, 70,
    ];

    /**
     * @var array
     */
    protected $noResultCodes = [
        100, 101,
    ];

    /**
     * sends a report request until one of the $noResultCodes comes in response.
     *
     * @var bool
     */
    protected $paginated = true;

    /**
     * Default filter parameters.
     *
     * @var array
     */
    protected $filters = [
        'type'    => 1,
        'version' => 1,
        'view'    => 2,
    ];

    /**
     * Sets the Netgsm service bulkId
     * If bulkId is set, type value is set to 0.
     *
     * @see https://www.netgsm.com.tr/dokuman/#http-get-rapor
     *
     * @param  mixed  $bulkId
     * @return AbstractNetgsmReport
     */
    public function setBulkId($bulkId): AbstractNetgsmReport
    {
        parent::setBulkId($bulkId);

        $this->setType(0);

        return $this;
    }

    /**
     * Processes and returns a report line.
     *
     * @param $line
     * @return array
     */
    public function processRow($line): array
    {
        return [
            'jobId'     => (int) $line->msginfo->jobID,
            'message'   => (string) $line->msginfo->msg,
            'startDate' => (string) $line->datetime->startdate,
            'endDate'   => (string) $line->datetime->stopdate,
            'status'    => (string) $line->msginfo->state,
            'total'     => (string) $line->msginfo->total,
            'header'    => (string) $line->msginfo->msgheader,
        ];
    }

    /**
     * Parses the XML response returned from the Netgsm API service.
     *
     * @param $response
     * @return Collection
     *
     * @throws \TarfinLabs\Netgsm\Exceptions\ReportException
     */
    public function parseResponse(string $response): Collection
    {
        $this->validateResponse($response);
        $response = utf8_encode(html_entity_decode($response));
        $xml = new SimpleXMLElement($response);

        $collection = new Collection();

        foreach ($xml->header->SMSReport as $line) {
            $item = $this->processRow($line);
            $collection->push($item);
        }

        return $collection;
    }
}
