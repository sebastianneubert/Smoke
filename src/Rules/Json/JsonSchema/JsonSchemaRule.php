<?php

namespace whm\Smoke\Rules\Json\JsonSchema;

use JsonSchema\Validator;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a JSON file is valid for a JSON schema file.
 */
class JsonSchemaRule extends StandardRule
{
    private $jsonSchemaFiles;

    protected $contentTypes = array('json');

    private $json_errors = array(
        JSON_ERROR_NONE => 'No Error',
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    public function init($jsonSchemaFiles)
    {
        $this->jsonSchemaFiles = $jsonSchemaFiles;
    }

    protected function doValidation(ResponseInterface $response)
    {
        $data = json_decode((string)$response->getBody());
        if ($data === null) {
            throw new ValidationFailedException("The given JSON data can not be validated (last error: '" . $this->json_errors[json_last_error()] . "').");
        } else {
            $errorStatus = false;
            $messageParts = array();

            foreach ($this->jsonSchemaFiles as $jsonSchemaFile) {
                $validator = new Validator();

                $jsonSchemaObject = (object) json_decode(file_get_contents($jsonSchemaFile['jsonfileurl']));

                $validator->check($data, $jsonSchemaObject);

                if (!$validator->isValid()) {
                    $errorStatus = true;
                    $errorMessage = '';
                    foreach ($validator->getErrors() as $error) {
                        $errorMessage = $errorMessage . sprintf("[%s] %s\n", $error['property'], $error['message']);
                    }
                    $messageParts[] = $jsonSchemaFile['jsonfilename'] . ' - ' . $jsonSchemaFile['jsonfileurl'] . '(last error: ' . $errorMessage . ').';
                }
            }

            if ($errorStatus === true) {
                $message = 'JSON file (' . (string) $response->getUri() . ')  does not validate against the following JSON Schema files: ' . implode(', ', $messageParts);
                throw new ValidationFailedException($message);
            }
        }
    }
}
