<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;

class Netgsm
{
    protected $client;
    protected $credentials;
    protected $defaults;

    /**
     * Netgsm constructor.
     * @param  Client  $client
     * @param  array  $credentials
     * @param  array  $defaults
     */
    public function __construct(Client $client, array $credentials = [], array $defaults = [])
    {
        $this->client = $client;
        $this->credentials = $credentials;
        $this->defaults = $defaults;
    }

    /**
     * @param  AbstractNetgsmMessage  $netgsmMessage
     * @return mixed
     * @throws CouldNotSendNotification
     * @throws Exceptions\IncorrectPhoneNumberFormatException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSms(AbstractNetgsmMessage $netgsmMessage)
    {
        try {

            $netgsmMessage
                ->setClient($this->client)
                ->setCredentials($this->credentials)
                ->setDefaults($this->defaults)
                ->send();

            return $netgsmMessage->getJobId();
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::NetgsmRespondedWithAnError($exception);
        }
    }

    /**
     * Get sending status report for messages between given dates.
     *
     * @param  AbstractNetgsmReport  $report
     * @param $startDate
     * @param $endDate
     * @param  array  $filters
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exceptions\ReportException
     */
    public function getReports(
        AbstractNetgsmReport $report,
        $startDate = null,
        $endDate = null,
        array $filters = []
    ): Collection {
        $report = $report->setClient($this->client);

        if ($startDate) {
            $report = $report->setStartDate($startDate);
        }

        if ($endDate) {
            $report = $report->setStartDate($startDate);
        }

        $report->setCredentials($this->credentials);

        if (count($filters) > 0) {
            foreach ($filters as $filter => $value) {
                if (!method_exists($report, 'set'.$filter)) {
                    continue;
                }

                $report->{'set'.$filter}($value);
            }
        }

        return $report->getReports();
    }
}
