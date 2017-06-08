<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class ValidRule extends StandardRule
{
    const SCHEMA = 'schema.xsd';
    const NON_STRICT_SCHEMA = 'nonStrictSchema.xsd';
    const INDEX = 'siteindex.xsd';

    private $strictMode;
    private $debug;

    protected $contentTypes = array('text/xml', 'application/xml');

    public function init($strictMode = true, $debug = false)
    {
        $this->debug = $debug;
        $this->strictMode = $strictMode;
    }

    private function getSchema($isIndex)
    {
        if ($isIndex) {
            return __DIR__ . '/' . self::INDEX;
        }

        if ($this->strictMode) {
            return __DIR__ . '/' . self::SCHEMA;
        } else {
            return __DIR__ . '/' . self::NON_STRICT_SCHEMA;
        }
    }

    private function validateBody($body, $filename, $isIndex = true)
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($body);

        $valid = @$dom->schemaValidate($this->getSchema($isIndex));

        if (!$valid) {
            $lastError = libxml_get_last_error();
            throw new ValidationFailedException(
                'The given sitemap file (' . $filename . ') did not validate against the sitemap schema (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
    }

    protected function doValidation(ResponseInterface $response)
    {
        $body = (string)$response->getBody();

        // sitemapindex or urlset
        if (preg_match('/<sitemapindex/', $body)) {
            $this->validateBody($body, (string)$response->getUri());
        } elseif (preg_match('/<urlset/', $body)) {
            $this->validateBody($body, (string)$response->getUri(), false);
        }
    }
}
