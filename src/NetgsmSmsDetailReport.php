<?php

namespace TarfinLabs\Netgsm;

use Illuminate\Support\Collection;
use SimpleXMLElement;

class NetgsmSmsDetailReport extends AbstractNetgsmReport
{
    protected $url = 'https://api.netgsm.com.tr/sms/report/detail';

    protected $retryCode = 100;

    /**
     * @var array
     */
    protected $filters = [
        'type'    => 1,
        'version' => 1,
        'view'    => 2
    ];

    /**
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
     * @param $line
     * @return array
     */
    public function processRow($line): array
    {
        return [
            'jobId'     => (integer) $line->msginfo->jobID,
            'message'   => (string) $line->msginfo->msg,
            'startDate' => (string) $line->datetime->startdate,
            'endDate'   => (string) $line->datetime->stopdate,
            'status'    => (string) $line->msginfo->state,
            'total'     => (string) $line->msginfo->total,
            'header'    => (string) $line->msginfo->msgheader
        ];
    }

    /**
     * @param $response
     * @return Collection
     * @throws Exceptions\ReportException
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
