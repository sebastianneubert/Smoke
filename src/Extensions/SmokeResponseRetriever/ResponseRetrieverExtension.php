<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phmLabs\Components\Annovent\Dispatcher;
use phmLabs\Components\Annovent\Event\Event;
use PhmLabs\Components\Init\Init;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;

class ResponseRetrieverExtension
{
    /**
     * @var Retriever
     */
    private $retriever;

    public function init(Configuration $_configuration, Dispatcher $_eventDispatcher)
    {
        if ($_configuration->hasSection('responseRetriever')) {
            $this->retriever = Init::initialize($_configuration->getSection('responseRetriever'));

            $_eventDispatcher->notify(new Event('ResponseRetriever.setSessionContainer.before', array('sessionContainer' => $_configuration->getSessionContainer())));

            $this->retriever->setSessionContainer($_configuration->getSessionContainer());
        } else {
            throw new \RuntimeException("No response retriever set. Please check the config file if a section 'responseRetriever' exists.");
        }
    }

    /**
     * @Event("Scanner.Init")
     */
    public function setRetriever(HttpClient $httpClient)
    {
        $this->retriever->setHttpClient($httpClient);
    }

    public function getRetriever()
    {
        return $this->retriever;
    }
}
