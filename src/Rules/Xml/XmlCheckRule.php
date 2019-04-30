<?php

namespace whm\Smoke\Rules\Xml;

use phm\HttpWebdriverClient\Http\Response\DomAwareResponse;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the found XML is well-formed.
 */
class XmlCheckRule extends StandardRule
{
    protected $contentTypes = array('text/xml', 'application/xml');

    /**
     * @param ResponseInterface $response
     * @throws ValidationFailedException
     */
    public function doValidation(ResponseInterface $response)
    {
        if ($response instanceof DomAwareResponse) {
            $body = (string)$response->getHtmlBody();
        } else {
            $body = (string)$response->getBody();
        }

        if ($body == "") {
            if ($response instanceof TimeoutAwareResponse) {
                if ($response->isTimeout()) {
                    return new CheckResult(CheckResult::STATUS_FAILURE, 'The request timed out and produced an empty XML document.');
                }
            }
            return new CheckResult(CheckResult::STATUS_FAILURE, 'The given XML document was empty.');
        }

        $domDocument = new \DOMDocument();
        $success = @$domDocument->loadXML($body);

        if (!$success) {
            $lastError = libxml_get_last_error();

            if ($lastError) {
                throw new ValidationFailedException('The xml file ' . $response->getUri() . ' is not well formed (last error: ' .
                    str_replace("\n", '', $lastError->message) . ').');
            } else {
                return new CheckResult(CheckResult::STATUS_FAILURE, 'Unknown error occured.');
            }

        }
    }
}
