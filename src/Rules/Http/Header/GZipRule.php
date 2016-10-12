<?php

namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if gzip compressions is activated.
 */
class GZipRule extends StandardRule
{
    public function doValidation(Response $response)
    {
        if (strpos($response->getContentType(), 'image') === false
            && strpos($response->getContentType(), 'pdf') === false
            && (string) $response->getBody() !== ''
        ) {
            if (!$response->hasHeader('Content-Encoding') || $response->getHeader('Content-Encoding')[0] !== 'gzip') {
                throw new ValidationFailedException('gzip compression not active');
            }
        }
    }
}
