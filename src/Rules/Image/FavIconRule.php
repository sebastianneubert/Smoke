<?php

namespace whm\Smoke\Rules\Image;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if the favicon that is used is the framework default.
 */
class FavIconRule extends StandardRule
{
    protected $contentTypes = array('image');

    private $favicons = array(
        '231567a8cc45c2cf966c4e8d99a5b7fd' => 'symfony2',
        '53a151ba1af3acdefe16fbbdad937ee4' => 'wordpress',
        'e6a9dc66179d8c9f34288b16a02f987e' => 'drupal',
        '8718c2998236c796896b725f264092ee' => 'typo3',
        '1da050bcdd95e30c3cd984cf1d450f81' => 'neos2',
        'abe604b0b1b232bc1d37ea23e619eb2a' => 'magento',
        'c1f20852dd1caf078f49de77a2de8e3f' => 'vbulletin',
        'cfe845e2eaaf1bf4e86b5921df1d39f3' => 'phpbb',
    );

    protected function doValidation(ResponseInterface $response)
    {
        if (strpos((string) $response->getUri(), 'favicon.ico') === false) {
            return;
        }

        $imageHash = md5((string)$response->getBody());

        $this->assert(!array_key_exists($imageHash, $this->favicons), 'Seems like you use the standard favicon of your framework (' . $this->favicons[$imageHash] . ').');
    }
}
