<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
use whm\Smoke\Http\Response;

/**
 * This rules counts the css files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class CssFileCountRule extends CountRule
{
    protected $errorMessage = 'Too many css files (%u) were found.';

    protected function getFilesToCount(Document $document, ResponseInterface $response)
    {
        return $document->getCssFiles($response->getUri());
    }
}
