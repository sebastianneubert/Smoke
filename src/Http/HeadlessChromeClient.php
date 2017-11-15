<?php

namespace whm\Smoke\Http;

use phm\HttpWebdriverClient\Http\Client\Decorator\FileCacheDecorator;
use phm\HttpWebdriverClient\Http\Client\Decorator\LoggerDecorator;
use phm\HttpWebdriverClient\Http\Client\HeadlessChrome\HeadlessChromeClient as HeadlessChrome;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class HeadlessChromeClient implements HttpClient
{
    /**
     * @var HeadlessChrome
     */
    private $chromeClient;

    public function init($nocache = false)
    {
        if ($nocache) {
            $this->chromeClient = new HeadlessChrome();
        } else {
            $chromeClient = new HeadlessChrome();
            $cachedClient = new FileCacheDecorator($chromeClient);
            $this->chromeClient = new LoggerDecorator($cachedClient);
        }
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
