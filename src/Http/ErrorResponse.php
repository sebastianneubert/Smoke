<?php

namespace whm\Smoke\Http;

use GuzzleHttp\Psr7\Response;
use phm\HttpWebdriverClient\Http\Response\RequestAwareResponse;
use phm\HttpWebdriverClient\Http\Response\UriAwareResponse;
use Psr\Http\Message\RequestInterface;

class ErrorResponse extends Response implements UriAwareResponse, RequestAwareResponse
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function getUri()
    {
        return $this->request->getUri();
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
