<?php

namespace whm\Smoke\Rules\Image;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if the size of an image is bigger than a given max value.
 */
class SizeRule extends StandardRule
{
    private $maxSize;

    protected $contentTypes = array('image');

    /**
     * @param int $maxSize The maximum size of an image file in kilobytes
     */
    public function init($maxSize = 100)
    {
        $this->maxSize = $maxSize;
    }

    protected function doValidation(ResponseInterface $response)
    {
        $size = strlen((string)$response->getBody()) / 1000;
        $this->assert($size <= $this->maxSize, 'The size of the file is too big (' . $size . ' KB)');
    }
}
