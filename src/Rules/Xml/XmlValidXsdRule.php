<?php

namespace whm\Smoke\Rules\Xml;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class XmlValidXsdRule extends StandardRule
{
    private $xsdFiles;

    protected $contentTypes = array('text/xml', 'application/xml');

    public function init($xsdFiles)
    {
        $this->xsdFiles = $xsdFiles;
    }

    /**
     * @param ResponseInterface $response
     * @throws ValidationFailedException
     */
    protected function doValidation(ResponseInterface $response)
    {
        $body = (string)$response->getBody();

        $dom = new \DOMDocument();
        @$dom->loadXML($body);

        $error = false;
        $messageParts = array();

        foreach ($this->xsdFiles as $xsdFile) {
            $valid = @$dom->schemaValidate($xsdFile['xsdfileurl']);

            if (!$valid) {
                $error = true;
                $lastError = libxml_get_last_error();

                $messageParts[] = $xsdFile['xsdfilename'] . ' - ' . $xsdFile['xsdfileurl'] . ' (last error: ' . str_replace("\n", '', $lastError->message) . ').';
            }
        }

        if ($error === true) {
            $message = 'XML file (' . (string)$response->getUri() . ')  does not validate against the following XSD files: ' . implode(', ', $messageParts);
            throw new ValidationFailedException($message);
        }
    }
}
