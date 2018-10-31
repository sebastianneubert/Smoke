<?php

namespace whm\Smoke\Extensions\SmokeStop;

use phmLabs\Components\Annovent\Dispatcher;
use phmLabs\Components\Annovent\Event\Event;
use PhmLabs\Components\Init\Init;
use whm\Smoke\Config\Configuration;

class StopExtension
{
    private $stopStrategies = array();

    /**
     * @param Configuration $_configuration
     * @param Dispatcher $_eventDispatcher
     * @throws \phmLabs\Components\Annovent\Exception
     */
    public function init(Configuration $_configuration, Dispatcher $_eventDispatcher)
    {
        if ($_configuration->hasSection('stop')) {
            $strategies = $_configuration->getSection('stop');

            foreach ($strategies as $name => $strategy) {
                $this->stopStrategies[$name] = Init::initialize($strategy);
                $_eventDispatcher->connectListener($this->stopStrategies[$name]);
            }
        }
    }

    /**
     * @Event("Scanner.Scan.isStopped")
     */
    public function isStopped(Event $event)
    {
        foreach ($this->stopStrategies as $strategy) {
            if ($strategy->isStopped()) {
                $event->setProcessed();
                return true;
            }
        }

        return false;
    }

    public function getStrategy($name)
    {
        if (array_key_exists($name, $this->stopStrategies)) {
            return $this->stopStrategies[$name];
        } else {
            throw new \RuntimeException("Strategy ('" . $name . "') not found. Available strategies are " . implode(', ', array_keys($this->stopStrategies)));
        }
    }
}
