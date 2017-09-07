<?php

namespace whm\Smoke\Http;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeClient as phmChromeClient;
use phm\HttpWebdriverClient\Http\Client\Decorator\FileCacheDecorator;
use phm\HttpWebdriverClient\Http\Client\Decorator\LoggerDecorator;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class ChromeClient implements HttpClient
{
    /**
     * @var phmChromeClient
     */
    private $chromeClient;

    public function init($host = 'localhost', $port = 4444)
    {
        $chromeClient = new phmChromeClient($host, $port);
        $cachedClient = new FileCacheDecorator($chromeClient);
        $this->chromeClient = new LoggerDecorator($cachedClient);
    }

    public function sendRequest(RequestInterface $request)
    {
        return $this->chromeClient->sendRequest($request);
    }

    public function sendRequests(array $requests)
    {
        return $this->chromeClient->sendRequests($requests);
    }

    public function getClientType()
    {
        return $this->chromeClient->getClientType();
    }

    public function close()
    {
        $this->chromeClient->close();
    }
}
