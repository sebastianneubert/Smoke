<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Koalamon;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Http\RequestFactory;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever as SmokeRetriever;

class Retriever implements SmokeRetriever
{
    private $apiKey;
    private $systems;
    private $project;

    /**
     * @var HttpAdapterInterface
     */
    private $client;

    const ENDPOINT_SYSTEMS = 'http://www.koalamon.com/rest/#project#/systems/?api_key=#api_key#';

    public function init($apiKey, $project)
    {
        $this->apiKey = $apiKey;
        $this->project = $project;
    }

    public function setHttpClient(HttpAdapterInterface $httpClient)
    {
        $this->client = $httpClient;
        $this->systems = $this->getSystems($httpClient);
    }

    public function getSystems(HttpAdapterInterface $httpClient)
    {
        $url = $this->prepareUrl(self::ENDPOINT_SYSTEMS);

        $systems = $httpClient->get(new Uri($url));

        return json_decode($systems->getBody(), true);
    }

    private function prepareUrl($url)
    {
        $preparedUrl = str_replace('#project#', $this->project, $url);
        $preparedUrl = str_replace('#api_key#', $this->apiKey, $preparedUrl);

        return $preparedUrl;
    }

    public function next()
    {
        if (empty($this->systems)) {
            return false;
        }

        $system = array_pop($this->systems);

        $request = RequestFactory::getRequest(new Uri($system['url']), 'GET', 'php://memory', ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive']);
        $responses = $this->client->sendRequests(array($request));

        return $responses[0];
    }

    public function getComingFrom(UriInterface $uri)
    {
        return new Uri('http://www.koalamon.com');
    }

    public function getOriginUri(UriInterface $uri)
    {
        return $uri;
    }
}
