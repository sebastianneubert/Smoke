<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use phm\HttpWebdriverClient\Http\Response\DomAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\CheckResult;
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

    private $gzipContentTypes = [
        'application/x-gzip',
        'application/gzip'
    ];

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

    /**
     * @param $body
     * @param $filename
     * @param bool $isIndex
     * @throws ValidationFailedException
     */
    private function validateBody($body, $filename, $isIndex = true)
    {
        if (!$this->strictMode) {
            $body = str_replace('<sitemapindex>', '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $body);
        }

        $dom = new \DOMDocument();

        @$dom->loadXML($body);

        $schema = $this->getSchema($isIndex);
        $valid = @$dom->schemaValidate($schema);

        if (!$valid) {
            $lastError = libxml_get_last_error();
            $message = 'The given sitemap file (' . $filename . ') did not validate against the sitemap schema (last error: ' . str_replace("\n", '', $lastError->message) . ').';
            return new CheckResult(CheckResult::STATUS_FAILURE, $message);
        } else {
            $message = 'The given sitemap file (' . $filename . ') is valid.';
            return new CheckResult(CheckResult::STATUS_SUCCESS, $message);

        }
    }

    /**
     * @param ResponseInterface $response
     * @throws ValidationFailedException
     */
    protected function doValidation(ResponseInterface $response)
    {
        if ($response instanceof DomAwareResponse) {
            $body = (string)$response->getHtmlBody();
        } else {
            $body = (string)$response->getBody();
        }

        if ($response->hasHeader('content-type')) {
            $contentType = $response->getHeader('content-type');
            if (is_array($contentType) && in_array(strtolower($contentType[0]), $this->gzipContentTypes)) {
                $body = gzdecode($response->getBody());
            }
        }

        // sitemapindex or urlset
        if (preg_match('/<sitemapindex/', $body)) {
            return $this->validateBody($body, (string)$response->getUri(), true);
        } elseif (preg_match('/<urlset/', $body)) {
            return $this->validateBody($body, (string)$response->getUri(), false);
        } else {
            throw new ValidationFailedException('The given document is not a valid sitemap. Nether sitemapindex nor urlset element was found. ');
        }
    }
}
