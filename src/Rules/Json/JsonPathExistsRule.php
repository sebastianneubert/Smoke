<?php

namespace whm\Smoke\Rules\Json;

use Peekmo\JsonPath\JsonStore;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if xpath is found in a html document.
 */
class JsonPathExistsRule extends StandardRule
{
    protected $contentTypes = ['json'];

    private $jsonPaths;

    public function init(array $jsonPaths)
    {
        $this->jsonPaths = $jsonPaths;
    }

    /**
     * @param $relation string
     * @param $value int
     * @param $count int
     *
     * @return bool
     */
    private function checkRelation($relation, $expected, $current)
    {
        switch ($relation) {
            case 'equals':
                if ($expected !== $current) {
                    return false;
                }
                break;
            case 'less than':
                if ($expected <= $current) {
                    return false;
                }
                break;
            case 'greater than':
                if ($expected >= $current) {
                    return false;
                }
                break;
        }

        return true;
    }

    public function doValidation(ResponseInterface $response)
    {
        $body = (string)$response->getBody();

        $json = json_decode($body);

        if (!$json) {
            throw new ValidationFailedException('The given json document is empty or not valid json.');
        }

        $store = new JsonStore($json);

        $error = false;
        $noCorrectJsonPaths = array();

        foreach ($this->jsonPaths as $path) {
            $jsonValue = $store->get($path['pattern']);
            $count = count($jsonValue);

            if ($jsonValue === false || (is_array($jsonValue) && empty($jsonValue))) {
                $error = true;
                $noCorrectJsonPaths[] = $path['pattern'] . ' (JSON path not found)';
            }
            if ($this->checkRelation($path['relation'], (int)$path['value'], $count) === false) {
                $error = true;
                $noCorrectJsonPaths[] = $path['pattern'] . ' (' . $count . ' elements found, expected ' . $path['relation'] . ' ' . $path['value'] . ')';
            }
        }

        if ($error === true) {
            $allNoCorrectJsonPaths = implode('", "', $noCorrectJsonPaths);
            throw new ValidationFailedException('Disonances with JSON Paths "' . $allNoCorrectJsonPaths . '!');
        }
    }
}
