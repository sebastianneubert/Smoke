<?php

namespace whm\Smoke\Rules\Html;

use phm\HttpWebdriverClient\Http\Response\ContentTypeAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given html document has a closing html tag </html>.
 */
class ClosingHtmlTagRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        if ($response instanceof ContentTypeAwareResponse) {

            // @todo this could be part of the StandardRule class
            $body = (string)$response->getBody();
            $body = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F,\xFF,\x8B]/', '', $body);

            if (($response->getStatusCode() < 300 || $response->getStatusCode() >= 400)
                && $response->getContentType() === 'text/html'
                && strlen($body) > 0
            ) {
                if (stripos($body, '</html>') === false) {
                    throw new ValidationFailedException('Closing html tag is missing (document length: ' . strlen($body) . ').');
                }
            }
        }
    }
}
