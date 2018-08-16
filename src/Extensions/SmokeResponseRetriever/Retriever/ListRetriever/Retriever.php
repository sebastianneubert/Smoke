<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\ListRetriever;

use GuzzleHttp\Psr7\Request;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\MultiRequestsException;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Http\RequestFactory;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever as SmokeRetriever;
use whm\Smoke\Extensions\SmokeResponseRetriever\RetrieverException;
use whm\Smoke\Scanner\SessionContainer;

class Retriever implements SmokeRetriever
{
    private $urls = [];

    /**
     * @var HttpClient
     */
    private $httpClient;
    private $urlStack = [];

    private $redirects = array();

    /**
     * @var SessionContainer
     */
    private $sessionContainer;

    public function init($urls)
    {
        if (is_array($urls)) {
            foreach ($urls as $key => $urlList) {
                foreach ($urlList as $url) {
                    if (is_array($url)) {
                        $uri = new Uri($url['url']);
                        if (array_key_exists('cookies', $url)) {
                            foreach ($url['cookies'] as $cookie) {
                                foreach ($cookie as $key => $value) {
                                    $uri->addCookie($key, $value);
                                }
                            }
                        }
                        if (array_key_exists('session', $url)) {
                            $sessionName = $url['session'];
                            $uri->setSessionIdentifier($sessionName);
                        }
                    } else {
                        $uri = new Uri($url);
                    }
                    $this->urls[(string)$uri] = ['url' => $uri, 'system' => $key];
                }
            }
            $this->urlStack = $this->urls;
        }
    }

    /**
     * @param Uri $uri
     *
     * @return Request
     */
    private function createRequest(Uri $uri)
    {
        $headers = ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive'];

        if ($uri->getSessionIdentifier()) {
            $session = $this->sessionContainer->getSession($uri->getSessionIdentifier());

            foreach ($session->getCookies() as $key => $value) {
                $uri->addCookie($key, $value);
            }
        }

        $request = RequestFactory::getRequest($uri, 'GET', 'php://memory', $headers);

        return $request;
    }

    public function next()
    {
        if (empty($this->urlStack)) {
            return false;
        }

        $url = array_pop($this->urlStack);

        if ($url['url'] instanceof UriInterface) {
            $urlObject = $url['url'];
        } else {
            $urlObject = new Uri($url['url']);
        }

        $request = $this->createRequest($urlObject);

        try {
            $responses = $this->httpClient->sendRequests(array($request));
        } catch (MultiRequestsException $e) {
            $exceptions = $e->getExceptions();
            /* @var \Exception[] $exceptions */

            $errorMessages = '';
            foreach ($exceptions as $exception) {
                // @fixme this must be part of the http client
                $message = $exception->getMessage();
                if (strpos($message, 'An error occurred when fetching the URI') === 0) {
                    $corruptUrl = substr($message, '41', strpos($message, '"', 41) - 41);
                    if (strpos($corruptUrl, '/') === 0) {

                        $mainUri = $request->getUri();
                        $this->redirects[(string)$mainUri->getScheme() . '://' . $mainUri->getHost() . $corruptUrl] = (string)$mainUri;

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

        if (empty($responses)) {
            return $this->next();
        } else {
            return $responses[0];
        }
    }

    public function getOriginUri(UriInterface $uri)
    {
        if (array_key_exists((string)$uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string)$uri]]['url'];
        }

        return $uri;
    }

    public function getComingFrom(UriInterface $uri)
    {
        return $uri;
    }

    public function getSystem(UriInterface $uri)
    {
        if (array_key_exists((string)$uri, $this->redirects)) {
            return $this->urls[$this->redirects[(string)$uri]]['system'];
        }

        return $this->urls[(string)$uri]['system'];
    }

    public function getSystems()
    {
        $systems = [];
        foreach ($this->urls as $key => $url) {
            $systems[] = $url['system'];
        }

        return $systems;
    }

    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setSessionContainer(SessionContainer $sessionContainer)
    {
        $this->sessionContainer = $sessionContainer;
    }

    public function getOccuredExceptions()
    {
        return [];
    }
}
