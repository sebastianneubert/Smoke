<?php

namespace whm\Smoke\Rules\Js;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule uses jshint to validate js files. It will find js syntax errors.
 */
class SyntaxRule implements Rule
{
    private $jsHintExecutable;
    private $tmpDir;

    /**
     * @param $jsHintExecutable string The path to the jshint executable
     * @param $tmpDir string The directory the temporary js file should be stored
     */
    public function init($jsHintExecutable = '', $tmpDir = '/tmp')
    {
        $this->jsHintExecutable = $jsHintExecutable;
        $this->tmpDir = $tmpDir;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response->getContentType() !== 'application/javascript') {
            return;
        }

        $filename = $this->tmpDir . DIRECTORY_SEPARATOR . md5((string)$response->getBody()) . '.js';
        file_put_contents($filename, (string)$response->getBody());
        $conf = __DIR__ . DIRECTORY_SEPARATOR . 'jsHint.conf';

        $command = $this->jsHintExecutable . ' --config ' . $conf . ' --verbose ' . $filename . ' | grep -E E[0-9]+.$';
        $validationResult = shell_exec($command);

        unlink($filename);

        if (!is_null($validationResult)) {
            $errorMsg = str_replace($filename . ':', '', $validationResult);
            throw new ValidationFailedException('JavaScript error found: ' . $errorMsg);
        }
    }
}
