<?php

namespace whm\Smoke\Rules\Http;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;

abstract class HttpsRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        if ('https' === $response->getUri()->getScheme()) {
            $certInfo = $this->getCertifacateInformation($response->getUri()->getHost());

            return $this->doValidate($certInfo);
        }
    }

    abstract protected function doValidate($certInfo);

    private function getCertifacateInformation($host)
    {
        $sslOptions = stream_context_create(array('ssl' => array('capture_peer_cert' => true)));

        $request = @stream_socket_client(
            'ssl://' . $host . ':443',
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $sslOptions
        );

        $content = @stream_context_get_params($request);

        $certInfo = openssl_x509_parse($content['options']['ssl']['peer_certificate']);

        return $certInfo;
    }
}