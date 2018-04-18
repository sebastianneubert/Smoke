<?php

namespace whm\Smoke\Http;

use phm\HttpWebdriverClient\Http\Client\Guzzle\GuzzleClient as phmGuzzleClient;
use phm\HttpWebdriverClient\Http\Client\Decorator\FileCacheDecorator;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class GuzzleClient implements HttpClient
{
    /**
     * @var phmGuzzleClient
     */
    private $guzzleClient;

    public function init($nocache = false)
    {
        if ($nocache) {
            $this->guzzleClient = new phmGuzzleClient();
        } else {
            $guzzleClient = new phmGuzzleClient;
            $this->guzzleClient = new FileCacheDecorator($guzzleClient);
        }
    }

    public function sendRequest(RequestInterface $request)
    {
        return $this->guzzleClient->sendRequest($request);
    }

    public function sendRequests(array $requests)
    {
        return $this->guzzleClient->sendRequests($requests);
    }

    public function getClientType()
    {
        return $this->guzzleClient->getClientType();
    }

    public function close()
    {

    }

    public function setOption($key, $value)
    {
        $this->guzzleClient->setOption($key, $value);
    }
}
