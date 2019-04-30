<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use phm\HttpWebdriverClient\Http\Response\EffectiveUriAwareResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;
use whm\Html\Uri;

class ForeignDomainFilter implements Filter
{
    /**
     * Filter foreign domains before request is fired
     *
     * @param UriInterface $currentUri
     * @param UriInterface $startUri
     * @return bool
     */
    public function isFiltered(UriInterface $currentUri, UriInterface $startUri)
    {
        /* @var $currentUri Uri */
        /* @var $startUri Uri */

        $startDomainElements = explode('.', $startUri->getHost());
        $currentDomainElements = explode('.', $currentUri->getHost());

        $startDomainLength = count($startDomainElements);
        $currentDomainLength = count($currentDomainElements);

        if ($currentDomainLength < $startDomainLength) {
            return true;
        }

        return $currentUri->getHost($startDomainLength) !== $startUri->getHost($startDomainLength);
    }

    public function isResponseFiltered(ResponseInterface $response, UriInterface $startUri)
    {
        if ($response instanceof EffectiveUriAwareResponse) {
            $isFiltered = $this->isFiltered($response->getEffectiveUri(), $startUri);
            return $isFiltered;
        }
        return false;
    }
}
