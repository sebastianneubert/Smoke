<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule will analyze any html document and checks if a given string is contained.
 */
class RegExNotExistsRule extends StandardRule
{
    private $regExs;

    protected $contentTypes = array('text/html');

    /**
     * @param int $string The string that the document must contain
     */
    public function init(array $regExs)
    {
        $this->regExs = $regExs;
    }

    protected function doValidation(Response $response)
    {
        foreach ($this->regExs as $regEx) {
            $this->assert(preg_match('^' . $regEx . '^', (string) $response->getBody()) === 0,
                'The given regular expression (' . $regEx . ') was found in this document.');
        }
    }
}
