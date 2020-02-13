<?php


namespace TarfinLabs\Netgsm;


use Illuminate\Support\Collection;

class NetgsmSmsReport extends AbstractNetgsmReport
{
    protected $url = 'https://api.netgsm.com.tr/sms/report';

    protected $noResultCodes = [
        60
    ];
    /**
     * @var array
     */
    protected $filters = [
        'type' => 2,
        'version' => 2
    ];

    /**
     * @var array
     */
    protected $columnMap = [
        0 => [
            'jobId' => 'integer',
            'phone' => 'string',
            'status' => 'integer'
        ],
        1 => [
            'jobId' => 'integer',
            'phone' => 'string',
            'status' => 'integer'
        ],
        2 => [
            'jobId' => 'integer',
            'phone' => 'string',
            'status' => 'integer',
            'operator_code' => 'integer',
            'length' => 'integer',
            'send_date' => 'date',
            'send_time' => 'date',
            'error_code' => 'integer'
        ],
        3 => [
            'jobId' => 'integer',
            'phone' => 'string',
            'status' => 'integer'
        ]
    ];

    /**
     * @var array
     */
    protected $columns = [
        'jobId'         => null,
        'phone'         => null,
        'status'        => null,
        'operator_code' => null,
        'length'        => null,
        'send_date'     => null,
        'send_time'     => null,
        'error_code'    => null
    ];

    /**
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
     * @param  string  $response
     * @return Collection
     * @throws Exceptions\ReportException
     */
    public function parseResponse(string $response): Collection
    {
        $collection = new Collection();
        if ($this->validateResponse($response)){
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
