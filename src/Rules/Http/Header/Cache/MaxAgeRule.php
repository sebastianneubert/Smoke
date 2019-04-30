<?php

namespace whm\Smoke\Rules\Http\Header\Cache;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * Checks if the max-age cache header is not 0.
 */
class MaxAgeRule implements Rule
{
    private $maxStatusCode;

    public function init($maxStatusCode = 200)
    {
        $this->maxStatusCode = $maxStatusCode;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() <= $this->maxStatusCode) {
            if ($response->hasHeader('Cache-Control') && false !== strpos($response->getHeader('Cache-Control')[0], 'max-age=0')) {
                throw new ValidationFailedException('max-age=0 was found');
            }
        }
    }
}
