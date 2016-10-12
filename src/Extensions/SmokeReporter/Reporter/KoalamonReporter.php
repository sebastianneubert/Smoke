<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Reporter as KoalaReporter;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class KoalamonReporter implements Reporter
{
    /**
     * @var Result[]
     */
    private $results = [];

    private $config;
    private $system;
    private $collect;
    private $identifier;
    private $systemUseRetriever;
    private $tool = 'smoke';
    private $groupBy;
    private $server;

    /**
     * @var KoalaReporter
     */
    private $reporter;

    /*
     * @var Retriever
     */
    private $retriever;

    private $output;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    public function init($apiKey, Configuration $_configuration, OutputInterface $_output, $server = 'https://webhook.koalamon.com', $system = '', $identifier = '', $tool = '', $collect = true, $systemUseRetriever = false, $groupBy = false)
    {
        $httpClient = new \GuzzleHttp\Client();
        $this->reporter = new KoalaReporter('', $apiKey, $httpClient, $server);

        $this->config = $_configuration;
        $this->systemUseRetriever = $systemUseRetriever;

        $this->system = $system;
        $this->collect = $collect;
        $this->identifier = $identifier;
        $this->groupBy = $groupBy;

        if ($tool) {
            $this->tool = $tool;
        }

        $this->server = $server;
        $this->output = $_output;
    }

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    /**
     * @param Rule [];
     *
     * @return array
     */
    private function getRuleKeys()
    {
        $keys = array();
        foreach ($this->config->getRules() as $key => $rule) {
            $keys[] = $key;
        }

        return $keys;
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $this->output->writeln('Sending results to ' . $this->server . " ... \n");

        if ($this->groupBy === 'prefix') {
            $this->sendGroupedByPrefix();
        } else {
            if ($this->collect) {
                $this->sendCollected();
            } else {
                $this->sendSingle();
            }
        }
    }

    private function getPrefix($string)
    {
        return substr($string, 0, strpos($string, '_'));
    }

    private function sendGroupedByPrefix()
    {
        $failureMessages = array();
        $counter = array();

        if ($this->systemUseRetriever) {
            $systems = $this->retriever->getSystems();
        } else {
            $systems = array($this->system);
        }

        foreach ($this->getRuleKeys() as $rule) {
            foreach ($systems as $system) {
                $identifier = $this->tool . '_' . $this->getPrefix($rule) . '_' . $system;
                $failureMessages[$identifier]['message'] = '';
                $failureMessages[$identifier]['system'] = $system;
                $failureMessages[$identifier]['tool'] = $this->getPrefix($rule);

                $counter[$identifier] = 0;
            }
        }

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $system = $this->system;
                    if ($this->systemUseRetriever) {
                        $system = $this->retriever->getSystem(($result->getUrl()));
                    }

                    $identifer = $this->tool . '_' . $this->getPrefix($ruleLKey) . '_' . $system;

                    if ($failureMessages[$identifer]['message'] === '') {
                        $failureMessages[$identifer]['message'] = 'The ' . $this->getPrefix($ruleLKey) . ' test for #system_name# failed.<ul>';
                    }
                    ++$counter[$identifer];
                    $failureMessages[$identifer]['message'] .= '<li>' . $message . '<br>url: ' . $result->getUrl() . ', coming from: ' . $this->retriever->getComingFrom($result->getUrl()) . '</li>';
                }
            }
        }

        foreach ($failureMessages as $key => $failureMessage) {
            if ($failureMessage['message'] !== '') {
                $this->send($this->identifier . '_' . $key, $failureMessage['system'], $failureMessage['message'] . '</ul>', self::STATUS_FAILURE, '', $counter[$key], $failureMessage['tool']);
            } else {
                $this->send($this->identifier . '_' . $key, $failureMessage['system'], '', self::STATUS_SUCCESS, '', 0, $failureMessage['tool']);
            }
        }
    }

    private function sendSingle()
    {
        $rules = $this->getRuleKeys();
        foreach ($this->results as $result) {
            $failedTests = array();
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $identifier = 'smoke_' . $ruleLKey . '_' . $result->getUrl();

                    if ($this->system === '') {
                        $system = str_replace('http://', '', $result->getUrl());
                    } else {
                        $system = $this->system;
                    }
                    $this->send($identifier, $system, 'smoke', $message, self::STATUS_FAILURE, (string) $result->getUrl());
                    $failedTests[] = $ruleLKey;
                }
            }
            foreach ($rules as $rule) {
                if (!in_array($rule, $failedTests, true)) {
                    $identifier = 'smoke_' . $rule . '_' . $result->getUrl();

                    if ($this->systemUseRetriever) {
                        $system = $this->retriever->getSystem($result->getUrl());
                    } elseif ($this->system === '') {
                        $system = str_replace('http://', '', $result->getUrl());
                    } else {
                        $system = $this->system;
                    }
                    $this->send($identifier, $system, 'smoke_' . $rule . '_' . $result->getUrl(), self::STATUS_SUCCESS, (string) $result->getUrl());
                }
            }
        }
    }

    private function sendCollected()
    {
        $failureMessages = array();
        $counter = array();

        foreach ($this->getRuleKeys() as $rule) {
            $failureMessages[$rule] = '';
            $counter[$rule] = 0;
        }

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $system = $this->system;
                    if ($this->systemUseRetriever) {
                        $system = $this->retriever->getSystem(new Uri($result->getUrl()));
                    }
                    if ($failureMessages[$ruleLKey] === '') {
                        $failureMessages[$ruleLKey]['message'] = '    The smoke test for #system_name# failed (Rule: ' . $ruleLKey . ').<ul>';
                    }
                    ++$counter[$ruleLKey];

                    $comingFrom = '';
                    if ($this->retriever->getComingFrom($result->getUrl())) {
                        $comingFrom = ', coming from: ' . $this->retriever->getComingFrom($result->getUrl());
                    }

                    $failureMessages[$ruleLKey]['message'] .= '<li>' . $message . ' (url: ' . $result->getUrl() . $comingFrom . ')</li > ';
                    $failureMessages[$ruleLKey]['system'] = $system;
                }
            }
        }

        foreach ($failureMessages as $key => $failureMessage) {
            if ($failureMessage !== '') {
                $this->send($this->identifier . '_' . $key, $this->system, $failureMessage['message'] . ' </ul > ', self::STATUS_FAILURE, '', $counter[$key]);
            } else {
                $this->send($this->identifier . '_' . $key, $this->system, '', self::STATUS_SUCCESS, '', 0);
            }
        }
    }

    private function send($identifier, $system, $message, $status, $url = '', $value = 0, $tool = null)
    {
        if (is_null($tool)) {
            $tool = $this->tool;
        }
        $event = new Event($identifier, $system, $status, $tool, $message, $value);
        $this->reporter->sendEvent($event);
    }
}
