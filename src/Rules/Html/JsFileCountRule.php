<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
use whm\Smoke\Http\Response;

/**
 * This rules counts the js files that are included in a document. If the number is higher
 * than a given value the test failes.
 */
class JsFileCountRule extends CountRule
{
    protected $errorMessage = 'Too many javascript files (%u) were found.';

    protected function getFilesToCount(Document $document, ResponseInterface $response)
    {
        return $document->getJsFiles($response->getUri());
    }
}
