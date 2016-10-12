<?php

/*
 * This rule will find external ressources on a https transfered page that are insecure (http).
 *
 * @author Nils Langner <nils.langner@phmlabs.com>
 * @inspiredBy Christian Haller
 */

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a https document uses http (insecure) ressources.
 */
class InsecureContentRule implements Rule
{
    public function validate(Response $response)
    {
        $request = $response->getRequest();

        if ('https' !== $request->getUri()->getScheme()) {
            return;
        }

        $htmlDocument = new Document($response->getBody());

        $resources = $htmlDocument->getDependencies($response->getRequest()->getUri(), false);

        foreach ($resources as $resource) {
            if ($resource->getScheme() && 'https' !== $resource->getScheme()) {
                throw new ValidationFailedException('At least one dependency was found on a secure url, that was transfered insecure (' . (string) $resource . ')');
            }
        }
    }
}
