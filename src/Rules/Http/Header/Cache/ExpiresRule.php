<?php

namespace whm\Smoke\Rules\Http\Header\Cache;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a expire header is in the past.
 */
class ExpiresRule implements Rule
{
    private $maxStatusCode;

    public function init($maxStatusCode = 200)
    {
        $this->maxStatusCode = $maxStatusCode;
    }

    public function validate(Response $response)
    {
        if ($response->getStatus() <= $this->maxStatusCode) {
            if ($response->hasHeader('Expires')) {
                $expireRaw = preg_replace('/[^A-Za-z0-9\-\/,]/', '', $response->getHeader('Expires')[0]);
                if ($expireRaw !== '') {
                    $expires = strtotime($response->getHeader('Expires')[0]);
                    if ($expires < time()) {
                        throw new ValidationFailedException('expires in the past');
                    }
                }
            }
        }
    }
}
