<?php

namespace whm\Smoke\Rules\Http\Header;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This class validates, whether the response has a specific status.
 * Can be used to test, if your homepage has a 200 status or your error page sends a 404.
 */
class HttpStatusRule implements Rule
{
    protected $expectedStatus = 200;

    /**
     * @param int $expectedStatus The expected HTTP-status
     */
    public function init($expectedStatus = 200)
    {
        $this->expectedStatus = $expectedStatus;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() !== $this->expectedStatus) {
            throw new ValidationFailedException('Status code ' . $response->getStatusCode() . ' found, ' . $this->expectedStatus . ' expected.');
        }
    }
}
