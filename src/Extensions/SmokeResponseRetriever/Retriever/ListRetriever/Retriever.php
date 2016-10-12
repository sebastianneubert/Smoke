<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\ListRetriever;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\MultiHttpAdapterException;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Http\RequestFactory;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever as SmokeRetriever;

class Retriever implements SmokeRetriever
{
    private $urls = [];
    private $httpClient;
    private $urlStack = [];

    private $redirects = array();

    public function init($urls)
    {
        if (is_array($urls)) {
            foreach ($urls as $key => $urlList) {
                foreach ($urlList as $url) {
                    $this->urls[$url] = ['url' => $url, 'system' => $key];
                }
            }
            $this->urlStack = $this->urls;
        }
    }

    public function next()
    {
        if (empty($this->urlStack)) {
            return false;
        }

        $url = array_pop($this->urlStack);

        $request = RequestFactory::getRequest(new Uri($url['url']), 'GET', 'php://memory', ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive']);

        try {
            $responses = $this->httpClient->sendRequests(array($request));
        } catch (MultiHttpAdapterException $e) {
            $exceptions = $e->getExceptions();
            $errorMessages = '';
            foreach ($exceptions as $exception) {
                // @fixme this must be part of the http client
                $message = $exception->getMessage();
                if (strpos($message, 'An error occurred when fetching the URI') === 0) {
                    $corruptUrl = substr($message, '41', strpos($message, '"', 41) - 41);
                    if (strpos($corruptUrl, '/') === 0) {
                        /* @var \Ivory\HttpAdapter\HttpAdapterException $exception */

                        $mainUri = $request->getUri();
                        $this->redirects[(string) $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl] = (string) $mainUri;

                        $this->urls[] = ['url' => $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl, 'system' => $url['system']];
                        $this->urlStack[] = ['url' => $mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl, 'system' => $url['system']];

                        return $this->next();
                    }

                    // the error handling should be done withing the calling class
                    echo "\n   " . $exception->getMessage() . "\n";

                    return $this->next();
                } else {
                    $errorMessages .= $exception->getMessage() . "\n";
                }
            }
            if ($errorMessages !== '') {
                throw new \RuntimeException($errorMessages);
            }
        }

        return $responses[0];
    }

    public function getOriginUri(UriInterface $uri)
    {
        if (array_key_exists((string) $uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string) $uri]]['url'];
        }

        return $uri;
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $uri;
    }

    public function getSystem(UriInterface $uri)
    {
        if (array_key_exists((string) $uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string) $uri]]['system'];
        }

        return $this->urls[(string) $uri]['system'];
    }

    public function getSystems()
    {
        $systems = [];
        foreach ($this->urls as $key => $url) {
            $systems[] = $url['system'];
        }

        return $systems;
    }

    public function setHttpClient(HttpAdapterInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
