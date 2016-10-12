<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class ValidRule extends StandardRule
{
    const SCHEMA = 'schema.xsd';

    protected $contentTypes = array('text/xml', 'application/xml');

    private function getSchema()
    {
        return __DIR__ . '/' . self::SCHEMA;
    }

    private function validateBody($body, $filename)
    {
        libxml_clear_errors();
        $dom = new \DOMDocument();
        @$dom->loadXML($body);
        $lastError = libxml_get_last_error();
        if ($lastError) {
            throw new ValidationFailedException(
                'The given sitemap file (' . $filename . ') is not well formed (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
        $valid = @$dom->schemaValidate($this->getSchema());
        if (!$valid) {
            $lastError = libxml_get_last_error();
            throw new ValidationFailedException(
                'The given sitemap file (' . $filename . ') did not validate against the sitemap schema (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
    }

    /**
     * @param string
     *
     * @return array
     */
    private function getLocations($body)
    {
        $locations = array();
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $xmlValues = json_decode($json, true);

        if (isset($xmlValues['sitemap']['loc'])) {
            $locations[] = $xmlValues['sitemap']['loc'];
        } else {
            foreach ($xmlValues['sitemap'] as $sitemap) {
                $locations[] = $sitemap['loc'];
            }
        }

        return $locations;
    }

    protected function doValidation(Response $response)
    {
        $body = $response->getBody();

        // sitemapindex or urlset
        if (preg_match('/<sitemapindex/', $body)) {
            $allSingleSitemapsUrls = $this->getLocations($body);
            if (count($allSingleSitemapsUrls) > 0) {
                // we only check the first sitemap we find
                $this->validateBody(file_get_contents($allSingleSitemapsUrls[0]), $allSingleSitemapsUrls[0]);
            }
        } elseif (preg_match('/<urlset/', $body)) {
            $this->validateBody($body, (string) $response->getUri());
        }
    }
}