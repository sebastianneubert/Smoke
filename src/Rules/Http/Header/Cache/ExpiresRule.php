<?php

namespace whm\Smoke\Rules\Http\Header\Cache;

use Psr\Http\Message\ResponseInterface;
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

    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() <= $this->maxStatusCode) {
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
