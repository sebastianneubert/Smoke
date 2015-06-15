<?php

namespace whm\Smoke\Rules\Html;

use Symfony\Component\DomCrawler\Crawler;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the no-index robots meta tag is not set.
 */
class NoIndexRule implements Rule
{
    public function validate(Response $response)
    {
        if ($response->getContentType() != "text/html") {
            return;
        }

        if ($response->getStatus() >= 300) {
            return;
        }

        $crawler = new Crawler($response->getBody());
        $metaTags = $crawler->filterXPath("//meta[@name='robots']/@content");

        foreach ($metaTags as $metaTag) {
            if (strpos($metaTag->nodeValue, 'no-index') !== false) {
                throw new ValidationFailedException('A meta tag "robots" with the value "no-index" was found');
            }
        }
    }
}
