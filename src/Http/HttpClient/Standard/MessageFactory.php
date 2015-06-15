<?php

namespace whm\Smoke\Http\HttpClient\Standard;

use Ivory\HttpAdapter\Message\RequestInterface;
use Ivory\HttpAdapter\Normalizer\HeadersNormalizer;
use Phly\Http\Stream;
use Psr\Http\Message\StreamInterface;
use whm\Smoke\Http\Response;

/**
 * MessageFactory.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class MessageFactory extends \Ivory\HttpAdapter\Message\MessageFactory
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(
        $statusCode = 200,
        $protocolVersion = RequestInterface::PROTOCOL_VERSION_1_1,
        array $headers = array(),
        $body = null,
        array $parameters = array()
    ) {
        return (new Response(
            $this->createStream($body),
            $statusCode,
            HeadersNormalizer::normalize($headers),
            $parameters
        ))->withProtocolVersion($protocolVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(
        $uri,
        $method = RequestInterface::METHOD_GET,
        $protocolVersion = RequestInterface::PROTOCOL_VERSION_1_1,
        array $headers = array(),
        $body = null,
        array $parameters = array()
    ) {
        return (new Request(
            $this->createUri($uri),
            $method,
            $this->createStream($body),
            HeadersNormalizer::normalize($headers),
            $parameters
        ))->withProtocolVersion($protocolVersion);
    }

    /**
     * Creates an uri.
     *
     * @param string $uri The uri.
     *
     * @return string The created uri.
     */
    private function createUri($uri)
    {
        if ($this->hasBaseUri() && (stripos($uri, $baseUri = (string) $this->getBaseUri()) === false)) {
            return $baseUri . $uri;
        }

        return $uri;
    }

    /**
     * Creates a stream.
     *
     * @param null|resource|string|\Psr\Http\Message\StreamInterface|null $body The body.
     *
     * @return \Psr\Http\Message\StreamInterface The stream.
     */
    private function createStream($body)
    {
        if ($body instanceof StreamInterface) {
            $body->rewind();

            return $body;
        }

        if (is_resource($body)) {
            return $this->createStream(new Stream($body));
        }

        $stream = new Stream('php://memory', 'rw');

        if ($body === null) {
            return $stream;
        }

        $stream->write((string) $body);

        return $this->createStream($stream);
    }
}
