<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use TarfinLabs\Netgsm\Balance\NetgsmAvailableCredit;
use TarfinLabs\Netgsm\Balance\NetgsmPackages;
use TarfinLabs\Netgsm\Exceptions\CouldNotSendNotification;
use TarfinLabs\Netgsm\Iys\NetgsmIys;
use TarfinLabs\Netgsm\Report\AbstractNetgsmReport;
use TarfinLabs\Netgsm\Sms\AbstractNetgsmMessage;

class Netgsm
{
    protected $client;
    protected $credentials;
    protected $defaults;

    /**
     * Netgsm constructor.
     *
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
     *
     * @throws CouldNotSendNotification
     * @throws Exceptions\IncorrectPhoneNumberFormatException
     * @throws GuzzleException
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
     * Get sms status report between given dates.
     *
     * @param  AbstractNetgsmReport  $report
     * @param  $startDate
     * @param  $endDate
     * @param  array  $filters
     * @return Collection
     *
     * @throws GuzzleException
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
                if (! method_exists($report, 'set'.$filter)) {
                    continue;
                }

                call_user_func([$report, 'set'.$filter], $value);
            }
        }

        return $report->getReports();
    }

    /**
     * Returns the remaining credits amount (TL) on the netgsm account.
     *
     * @return string
     *
     * @throws Exceptions\NetgsmException
     * @throws GuzzleException
     */
    public function getCredit()
    {
        $creditService = new NetgsmAvailableCredit();
        $creditService->setClient($this->client);
        $creditService->setCredentials($this->credentials);

        return $creditService->getCredit();
    }

    /**
     * Returns the available package list and their balances on the netgsm account.
     *
     * @return array
     *
     * @throws Exceptions\NetgsmException
     * @throws GuzzleException
     */
    public function getAvailablePackages(): Collection
    {
        $packageService = new NetgsmPackages();
        $packageService->setClient($this->client);
        $packageService->setCredentials($this->credentials);

        return collect($packageService->getPackages());
    }

    /**
     * @return NetgsmIys
     */
    public function iys(): NetgsmIys
    {
        $iysService = new NetgsmIys();
        $iysService->setClient($this->client)->setCredentials($this->credentials);

        return $iysService;
    }
}
