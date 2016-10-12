<?php

namespace whm\Smoke\Rules\Xml;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the found XML is well-formed.
 */
class XmlCheckRule extends StandardRule
{
    protected $contentTypes = array('text/xml', 'application/xml');

    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        $success = @$domDocument->loadXML((string) $response->getBody());

        if (!$success) {
            $lastError = libxml_get_last_error();

            throw new ValidationFailedException('The xml file ' . $response->getUri() . ' is not well formed (last error: ' .
                str_replace("\n", '', $lastError->message) . ').');
        }
    }
}
