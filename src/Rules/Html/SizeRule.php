<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule calculates the size of a html document. If the document is bigger than a given value
 * the test will fail.
 */
class SizeRule extends StandardRule
{
    private $maxSize;

    protected $contentTypes = array('text/html');

    /**
     * @param int $maxSize The maximum size of a html file in kilobytes
     */
    public function init($maxSize = 200)
    {
        $this->maxSize = $maxSize;
    }

    protected function doValidation(ResponseInterface $response)
    {
        $size = strlen((string)$response->getBody()) / 1000;
        $this->assert($size <= $this->maxSize, 'The size of this html file is too big (' . $size . ' KB)');
    }
}
