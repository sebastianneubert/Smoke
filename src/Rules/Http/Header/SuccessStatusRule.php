<?php

namespace whm\Smoke\Rules\Http\Header;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if the http status code of a request is less than 400.
 */
class SuccessStatusRule implements Rule
{
    private $maxStatusCode;

    public function init($maxStatusCode = 399)
    {
        $this->maxStatusCode = $maxStatusCode;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() > $this->maxStatusCode) {
            throw new ValidationFailedException('Status code ' . $response->getStatusCode() . ' found.');
        }
    }
}
