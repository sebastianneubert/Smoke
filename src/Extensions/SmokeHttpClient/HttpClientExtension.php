<?php

namespace whm\Smoke\Extensions\SmokeHttpClient;

use Ivory\HttpAdapter\HttpAdapterInterface;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;

class HttpClientExtension
{
    /**
     * @var Retriever
     */
    private $retriever;

    public function init()
    {
        throw new \RuntimeException('Extension is not ready to use yet.');
    }

    /**
     * @Event("Scanner.Init")
     */
    public function setRetriever(HttpAdapterInterface $httpClient)
    {

    }
}
