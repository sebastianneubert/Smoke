<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule will analyze any html document and checks if a given string is contained.
 */
class StringNotExistsRule extends StandardRule
{
    protected $contentTypes = 'text/html';

    private $string;

    /**
     * @param int $string The string that the document must contain
     */
    public function init($string)
    {
        $this->string = $string;
    }

    public function doValidation(ResponseInterface $response)
    {
        $this->assert(strpos((string)$response->getBody(), $this->string) !== false,
            'The given string (' . $this->string . ') was found in this document.');
    }
}
