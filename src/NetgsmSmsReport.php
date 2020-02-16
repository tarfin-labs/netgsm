<?php


namespace TarfinLabs\Netgsm;


use Illuminate\Support\Collection;

class NetgsmSmsReport extends AbstractNetgsmReport
{
    protected $url = 'https://api.netgsm.com.tr/sms/report';

    /**
     * @var array
     */
    protected $errorCodes = [
        30, 70, 100, 101
    ];

    /**
     * @var array
     */
    protected $noResultCodes = [
        60
    ];
    /**
     * @var array
     */
    protected $filters = [
        'type'    => 2,
        'version' => 2
    ];

    /**
     * @var array
     */
    protected $columnMap = [
        0 => [
            'jobId'  => 'integer',
            'phone'  => 'string',
            'status' => 'integer'
        ],
        1 => [
            'jobId'  => 'integer',
            'phone'  => 'string',
            'status' => 'integer'
        ],
        2 => [
            'jobId'        => 'integer',
            'phone'        => 'string',
            'status'       => 'integer',
            'operatorCode' => 'integer',
            'length'       => 'integer',
            'startDate'    => 'date',
            'startTime'    => 'date',
            'errorCode'    => 'integer'
        ],
        3 => [
            'jobId'  => 'integer',
            'phone'  => 'string',
            'status' => 'integer'
        ]
    ];

    /**
     * @var array
     */
    protected $columns = [
        'jobId'        => null,
        'phone'        => null,
        'status'       => null,
        'operatorCode' => null,
        'length'       => null,
        'startDate'    => null,
        'startTime'    => null,
        'errorCode'    => null
    ];

    /**
     * Sets the Netgsm service bulkId
     * If bulkId is set, type value is set to 1.
     * @see https://www.netgsm.com.tr/dokuman/#http-get-rapor
     *
     * @param  string  $bulkId
     * @return AbstractNetgsmReport
     */
    public function setBulkId($bulkId): AbstractNetgsmReport
    {
        parent::setBulkId($bulkId);

        $this->setType(1);

        return $this;
    }

    /**
     * formats the fields and adds them according to the api version defined.
     *
     * @param  string  $line
     * @return array
     */
    protected function processRow(string $line): array
    {
        $item = [];
        $version = $this->filters['version'];
        $columnMapByVersion = $this->columnMap[$version];
        $lineColumns = explode(' ', $line);
        foreach ($this->columns as $column => $val) {
            $columnPos = array_search($column, array_keys($columnMapByVersion));
            $columnValue = $columnPos !== false ?
                $this->formatValue($lineColumns[$columnPos],
                    $columnMapByVersion[$column]) : null;
            $item[$column] = $columnValue;
        }

        return $item;
    }

    /**
     * Parses the string response (separated by newline and whitespaces!) returned from the Netgsm API service.
     *
     * @param  string  $response
     * @return Collection
     * @throws Exceptions\ReportException
     */
    public function parseResponse(string $response): Collection
    {
        $collection = new Collection();
        if ($this->validateResponse($response)) {
            $response = rtrim($response, '<br>');
            $lines = explode('<br>', $response);
            foreach ($lines as $line) {
                $item = $this->processRow($line);
                $collection->push($item);
            }
        }

        return $collection;
    }
}
