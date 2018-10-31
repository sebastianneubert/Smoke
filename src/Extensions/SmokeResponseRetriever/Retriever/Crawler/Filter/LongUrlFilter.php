<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use phm\HttpWebdriverClient\Http\Response\EffectiveUriAwareResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;
use whm\Html\Uri;

class LongUrlFilter implements Filter
{
    /**
     * Filter urls that are too long. Mostly that is a sign of infinite loops
     *
     * @param UriInterface $currentUri
     * @param UriInterface $startUri
     * @return bool
     */
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        return strlen((string)$currentUri) > 255;
    }

    public function isResponseFiltered(ResponseInterface $response, UriInterface $startUri)
    {
        return false;
    }
}
