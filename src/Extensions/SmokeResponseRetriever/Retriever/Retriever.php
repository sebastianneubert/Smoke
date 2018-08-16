<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Smoke\Extensions\SmokeResponseRetriever\RetrieverException;
use whm\Smoke\Scanner\SessionContainer;

interface Retriever
{
    public function setHttpClient(HttpClient $httpClient);

    /**
     * @return ResponseInterface
     */
    public function next();

    public function setSessionContainer(SessionContainer $sessionContainer);

    public function getComingFrom(UriInterface $uri);

    public function getOriginUri(UriInterface $uri);

    /**
     * @return RetrieverException[]
     */
    public function getOccuredExceptions();
}
