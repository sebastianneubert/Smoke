<?php

namespace whm\Smoke\Yaml;

use Symfony\Component\Yaml\Yaml;

class EnvAwareYaml
{
    public static function parse($fileContent)
    {
        preg_match_all('^\${(.*)}^', $fileContent, $matches);

        foreach ($matches[1] as $varName) {
            if (!getenv($varName)) {
                throw new \RuntimeException('The mandatory env variable (' . $varName . ') from the config file was not set.');
            }

            $fileContent = str_replace('${' . $varName . '}', getenv($varName), $fileContent);
        }

        return Yaml::parse($fileContent);
    }
}
