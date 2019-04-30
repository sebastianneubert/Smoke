<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule will analyze any html document and checks if a given string is contained.
 */
class RegExExistsRule extends StandardRule
{
    private $regExs;

    protected $contentTypes = array('text/html', 'application/json', 'application/xml');

    /**
     * @param int $string The string that the document must contain
     */
    public function init(array $regExs)
    {
        $regExArray = array();

        foreach ($regExs as $regEx) {
            if (array_key_exists('regex', $regEx)) {
                $isRegex = $regEx['isRegex'] == 'on';
                $regExArray[] = ['pattern' => $regEx['regex'], 'isRegEx' => $isRegex];
            } else {
                $regExArray[] = $regEx;
            }
        }

        $this->regExs = $regExArray;
    }

    /**
     * @param ResponseInterface $response
     * @throws ValidationFailedException
     */
    protected function doValidation(ResponseInterface $response)
    {
        $errors = [];

        foreach ($this->regExs as $regEx) {
            if ($regEx['isRegEx']) {
                $pattern = str_replace('', '\\~', $regEx['pattern']);

                if (preg_match('~' . $pattern . '~', (string)$response->getBody()) === 0) {
                    $errors[] = 'Regular expression: ' . $regEx['pattern'];
                }
            } else {
                if (preg_match('^' . preg_quote($regEx['pattern']) . '^', (string)$response->getBody()) === 0) {
                    $errors[] = 'Text: ' . $regEx['pattern'];
                }
            }
        }

        if (count($errors) > 0) {
            $errorString = 'The following text elements were not found: <ul>';

            foreach ($errors as $error) {
                $errorString .= '<li>' . $error . '</li>';
            }

            $errorString .= '</ul>';

            throw new ValidationFailedException($errorString);
        }
    }
}
