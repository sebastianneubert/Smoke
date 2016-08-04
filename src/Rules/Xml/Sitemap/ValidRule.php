<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a rss feed is valid.
 */
class ValidRule implements Rule
{
    const SITEINDEX = 'siteindex.xsd';
    const SITEMAP = 'sitemap.xsd';

    private function getSchema($name = 'index')
    {
        switch ($name) {
            case 'map':
                return __DIR__ . '/' . self::SITEMAP;
            case 'index':
            default:
                return __DIR__ . '/' . self::SITEINDEX;
        }
    }

    public function validate(Response $response)
    {
        if ($response->getContentType() !== 'text/xml') {
            return;
        }

        $body = $response->getBody();

        if (0 === preg_match('/<(sitemapindex|urlset)/', $body)) {
            return;
        }

        libxml_clear_errors();
        $dom = new \DOMDocument();
        @$dom->loadXML($body);
        $lastError = libxml_get_last_error();
        if ($lastError) {
            throw new ValidationFailedException(
                'The given sitemap file is not well formed (last error: ' .
                str_replace("\n", '', $lastError->message) . ').');
        }

        if (preg_match('/<sitemapindex/', $body)) {
            $valid = @$dom->schemaValidate($this->getSchema('index'));
        } elseif (preg_match('/<urlset/', $body)) {
            $valid = @$dom->schemaValidate($this->getSchema('map'));
        } else {
            $valid = false;
        }

        if (!$valid) {
            $lastError = libxml_get_last_error();
            $lastErrorMessage = str_replace("\n", '', $lastError->message);
            throw new ValidationFailedException(
                'The given sitemap file did not validate vs. ' .
                $this->getSchema() . ' (last error: ' .
                $lastErrorMessage . ').');
        }
    }
}
