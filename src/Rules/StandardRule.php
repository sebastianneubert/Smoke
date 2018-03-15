<?php

namespace whm\Smoke\Rules;

use phm\HttpWebdriverClient\Http\Response\ContentTypeAwareResponse;
use Psr\Http\Message\ResponseInterface;

abstract class StandardRule implements Rule
{
    protected $contentTypes = array();

    public function validate(ResponseInterface $response)
    {
        /** @var ContentTypeAwareResponse $response */

        if (count($this->contentTypes) > 0) {
            $valid = false;
            foreach ($this->contentTypes as $validContentType) {
                if (strpos(strtolower($response->getContentType()), strtolower($validContentType)) !== false) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return;
            }
        }

        return $this->doValidation($response);
    }

    abstract protected function doValidation(ResponseInterface $response);

    /**
     * @param $valueToBeTrue
     * @param $errorMessage
     * @throws ValidationFailedException
     */
    protected function assert($valueToBeTrue, $errorMessage)
    {
        if (!$valueToBeTrue) {
            throw new ValidationFailedException($errorMessage);
        }
    }
}
