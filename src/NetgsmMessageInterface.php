<?php


namespace TarfinLabs\Netgsm;


use GuzzleHttp\Client;

interface NetgsmMessageInterface
{
    public function setClient(Client $client):self;
    public function setCredentials(array $credentials):self;
    public function send():self;
    public function getJobId():?string;
    public function setTo(string $to):self;
}
