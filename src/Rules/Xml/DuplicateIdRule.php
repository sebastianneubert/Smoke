<?php

namespace whm\Smoke\Rules\Xml;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a css id is duplicated.
 */
class DuplicateIdRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        if ($response->getContentType() !== 'text/html') {
            return;
        }

        $crawler = new Crawler((string)$response->getBody());

        $idList = $crawler->filterXPath('//*[@id!=""]');

        $foundIds = array();
        $duplicatedIds = array();

        foreach ($idList as $idElement) {
            $id = $idElement->getAttribute('id');
            if (array_key_exists($id, $foundIds)) {
                $duplicatedIds[$id] = true;
            } else {
                $foundIds[$id] = true;
            }
        }

        if (count($duplicatedIds) > 0) {
            unset($duplicatedIds['']);
            throw new ValidationFailedException('Duplicate ids found (' . implode(', ', array_keys($duplicatedIds)) . ')');
        }
    }
}
