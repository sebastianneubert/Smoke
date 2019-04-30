<?php

namespace whm\Smoke\Rules\Json;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given json file is valid.
 */
class ValidRule extends StandardRule
{
    protected $contentTypes = ['json'];

    private $json_errors = array(
        JSON_ERROR_NONE => 'No Error',
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    public function doValidation(ResponseInterface $response)
    {
        $result = json_decode((string)$response->getBody());
        if ($result === null) {
            throw new ValidationFailedException("The given JSON data can not be validated (last error: '" . $this->json_errors[json_last_error()] . "').");
        }
    }
}
