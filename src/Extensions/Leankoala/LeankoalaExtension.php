<?php

namespace whm\Smoke\Extensions\Leankoala;

use whm\Smoke\Config\Configuration;

class LeankoalaExtension
{
    private $systems = array();

    public function init(Configuration $_configuration)
    {
        if ($_configuration->hasSection('Leankoala')) {
            $config = $_configuration->getSection('Leankoala');
            $this->systems = $config['systems'];
        }
    }

    public function getSystem($componentId)
    {
        if (array_key_exists($componentId, $this->systems)) {
            return (string) $this->systems[$componentId];
        } else {
            return $componentId;
        }
    }

    /**
     * @Event("Scanner.CheckResponse.isFiltered")
     */
    public function nullFunction()
    {
    }
}
