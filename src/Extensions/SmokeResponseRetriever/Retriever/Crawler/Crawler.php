<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use PhmLabs\Components\Init\Init;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Crawler as whmCrawler;
use whm\Crawler\PageContainer\PageContainer;
use whm\Crawler\PageContainer\PatternAwareContainer;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\CrawlingRetriever;
use whm\Smoke\Extensions\SmokeResponseRetriever\RetrieverException;
use whm\Smoke\Scanner\SessionContainer;

class Crawler implements CrawlingRetriever
{
    private $startPage;
    private $httpClient;
    private $parallelRequests;

    private $started = false;
    private $filters;

    /**
     * @var PageContainer
     */
    private $pageContainer;

    /**
     * @var \whm\Crawler\Crawler
     */
    private $crawler;

    public function init(array $filters, $pageContainer, $startPage = null, $parallelRequests = 5)
    {
        $this->filters = Init::initializeAll($filters);
        if (!is_null($startPage)) {
            $this->startPage = new Uri($startPage);
        }
        $this->initPageContainer($pageContainer);
        $this->parallelRequests = $parallelRequests;
    }

    public function addPage(UriInterface $uri)
    {
        $this->pageContainer->push($uri, true);
    }

    private function initPageContainer($pageContainerArray)
    {
        $this->pageContainer = Init::initialize($pageContainerArray);

        // @todo this should be done inside a factory
        if ($this->pageContainer instanceof PatternAwareContainer) {
            if (array_key_exists('parameters', $pageContainerArray) && array_key_exists('pattern', $pageContainerArray['parameters'])) {
                foreach ($pageContainerArray['parameters']['pattern'] as $name => $pattern) {
                    $this->pageContainer->registerPattern($name, $pattern);
                }
            }
        }
    }

    public function getStartPage()
    {
        return $this->startPage;
    }

    public function setStartPage(UriInterface $startPage)
    {
        $this->startPage = $startPage;
    }

    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return ResponseInterface
     */
    public function next()
    {
        if (!$this->started) {
            $this->started = true;

            if (is_null($this->startPage)) {
                throw new \RuntimeException('The crawler you are using needs a start page to work, but it is not defined. ');
            }

            $this->crawler = new whmCrawler($this->httpClient, $this->pageContainer, $this->startPage, $this->parallelRequests);

            foreach ($this->filters as $filter) {
                $this->crawler->addFilter($filter);
            }
        }

        return $this->crawler->next();
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $this->crawler->getComingFrom($uri);
    }

    public function getOriginUri(UriInterface $uri)
    {
        return $uri;
    }

    public function setSessionContainer(SessionContainer $sessionContainer)
    {
    }

    public function getOccuredExceptions()
    {
        return [];
    }
}
