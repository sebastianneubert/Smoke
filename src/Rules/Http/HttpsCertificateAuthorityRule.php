<?php

namespace whm\Smoke\Rules\Http;

use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given https certificate is cretaed by a special authority.
 */
class HttpsCertificateAuthorityRule extends HttpsRule
{
    private $authorityName;

    /**
     * @param string $authorityName authority name
     */
    public function init($authorityName)
    {
        $this->authorityName = $authorityName;
    }

    protected function doValidate($certInfo)
    {
        if (array_key_exists('issuer', $certInfo) and array_key_exists('CN', $certInfo['issuer'])) {
            if ($certInfo['issuer']['CN'] !== $this->authorityName) {
                throw new ValidationFailedException('Expected authority was "' . $this->authorityName . '", "' . $certInfo['issuer']['CN'] . '" found.');
            }
        } else {
            throw new ValidationFailedException('Expected authority was "' . $this->authorityName . '". No authority found.');
        }
    }
}
