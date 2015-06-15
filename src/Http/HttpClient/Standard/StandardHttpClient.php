<?php

namespace whm\Smoke\Http\HttpClient\Standard;

use whm\Smoke\Http\HttpClient\HttpClient;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\MultiHttpAdapterException;
use whm\Smoke\Http\Request;

/**
 * HttpClient.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class StandardHttpClient implements HttpClient
{
    /**
     * @var HttpAdapterInterface
     */
    private $adapter;

    public function __construct(HttpAdapterInterface $adapter)
    {
        $adapter->getConfiguration()->setMessageFactory(new MessageFactory());
        $this->adapter = $adapter;
    }

    /**
     * @param array $uris
     *
     * @return Response[]
     */
    public function request(array $uris)
    {
        $requests = [];

        foreach ($uris as $uri) {
            $requests[] = new Request($uri, 'GET', 'php://memory', ['Accept-Encoding' => 'gzip'], []);
        }

        try {
            $responses = $this->adapter->sendRequests($requests);
        } catch (MultiHttpAdapterException $e) {
            $responses = $e->getResponses();
            //$exceptions = $e->getExceptions();
        }

        return $responses;
    }
}
