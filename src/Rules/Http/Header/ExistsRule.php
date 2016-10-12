<?php

namespace whm\Smoke\Rules\Http\Header;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if gzip compressions is activated.
 */
class ExistsRule extends StandardRule
{
    private $checkedHeaders;

    public function init(array $checkedHeaders)
    {
        $this->checkedHeaders = $checkedHeaders;
    }

    public function doValidation(Response $response)
    {
        // @todo the test should not fail with the first not found header

        foreach ($this->checkedHeaders as $headerConfig) {
            if (!$response->hasHeader($headerConfig['key'])) {
                throw new ValidationFailedException('Header not found (' . $headerConfig['key'] . ')');
            }

            $currentValue = $response->getHeader($headerConfig['key'])[0];

            if (!preg_match('^' . $headerConfig['value'] . '^', $currentValue, $matches)) {
                throw new ValidationFailedException('Header "' . $headerConfig['key'] . '" does not match "' . $headerConfig['value'] . '". Current value is "' . $currentValue . '"');
            }
        }
    }
}
