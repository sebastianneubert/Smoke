<?php

namespace whm\Smoke\Rules\Http\Header\Cache;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if there are no "pragma: no-cache" or "cache-control: no-cache" header are set.
 */
class PragmaNoCacheRule implements Rule
{
    private $maxStatusCode;

    public function init($maxStatusCode = 200)
    {
        $this->maxStatusCode = $maxStatusCode;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() <= $this->maxStatusCode) {
            if ($response->hasHeader('Pragma') && 'no-cache' === $response->getHeader('Pragma')[0]) {
                throw new ValidationFailedException('pragma:no-cache was found');
            }

            if ($response->hasHeader('Cache-Control') && false !== strpos($response->getHeader('Cache-Control')[0], 'no-cache')) {
                throw new ValidationFailedException('cache-control:no-cache was found');
            }
        }
    }
}
