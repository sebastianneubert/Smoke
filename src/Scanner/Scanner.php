<?php

namespace whm\Smoke\Scanner;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Response\InteractiveResponse;
use phm\HttpWebdriverClient\Http\Response\SequenceAwareResponse;
use phmLabs\Components\Annovent\Dispatcher;
use phmLabs\Components\Annovent\Event\Event;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Http\ErrorResponse;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\ErrorResponseAwareRule;
use whm\Smoke\Rules\Rule;


class Scanner
{
    const ERROR = 'error';
    const PASSED = 'passed';

    /**
     * @var Rule[]
     */
    private $rules;
    private $eventDispatcher;

    private $responseRetriever;

    private $status = 0;

    public function __construct(array $rules, HttpClient $client, Dispatcher $eventDispatcher, Retriever $responseRetriever)
    {
        $eventDispatcher->simpleNotify('Scanner.Init', array('rules' => $rules, 'httpClient' => $client, 'dispatcher' => $eventDispatcher));

        $this->initRules($rules, $client);

        $this->eventDispatcher = $eventDispatcher;

        $this->responseRetriever = $responseRetriever;

        $this->eventDispatcher->simpleNotify('Scanner.Init.ResponseRetriever', array('responseRetriever' => $this->responseRetriever));
    }

    private function initRules($rules, HttpClient $client)
    {
        $this->rules = $rules;
        foreach ($this->rules as $rule) {
            if ($rule instanceof ClientAware) {
                $rule->setClient($client);
            }
        }
    }

    public function scan()
    {
        $this->eventDispatcher->simpleNotify('Scanner.Scan.Begin');

        while (($response = $this->responseRetriever->next()) && !$this->eventDispatcher->notifyUntil(new Event('Scanner.Scan.isStopped'))) {

            // this is the url filter
            if ($this->eventDispatcher->notifyUntil(new Event('Scanner.ProcessHtml.isFiltered', array('uri' => $response->getUri())))) {
                continue;
            }

            if ($response instanceof ErrorResponse) {
                $this->eventDispatcher->simpleNotify('Scanner.Scan.Response.Error', array('response' => $response));
            }

            $results = $this->checkResponse($response);

            if (count($results) === 0) {
                $checkResult = new CheckResult(CheckResult::STATUS_NONE, '');
                $checkResult->setResponse($response);
                $results = [$checkResult];
            }

            $this->eventDispatcher->simpleNotify('Scanner.Scan.Validate', array('results' => $results, 'response' => $response));
        }

        $this->eventDispatcher->simpleNotify('Scanner.Scan.Exceptions.Handle', array('occuredExceptions' => $this->responseRetriever->getOccuredExceptions()));

        $this->eventDispatcher->simpleNotify('Scanner.Scan.Finish');
    }

    public function getStatus()
    {
        return $this->status;
    }

    private function checkResponse(ResponseInterface $response)
    {
        $results = [];

        foreach ($this->rules as $name => $rule) {
            if ($this->eventDispatcher->notifyUntil(new Event('Scanner.CheckResponse.isFiltered', array('ruleName' => $name, 'rule' => $rule, 'response' => $response)))) {
                continue;
            }

            if ($response instanceof ErrorResponse && !($rule instanceof ErrorResponseAwareRule)) {
                continue;
            }

            try {
                $result = $rule->validate($response);
                if (!$result) {
                    $result = new CheckResult(CheckResult::STATUS_SUCCESS, 'Check successful.');
                }
            } catch (\Exception $e) {
                $result = new CheckResult(CheckResult::STATUS_FAILURE, 'An error occurred: ' . $e->getMessage());
                $this->eventDispatcher->simpleNotify('Scanner.CheckResponse.Rule.Error', array('checkResult' => $result, 'ruleName' => $name, 'exception' => $e));
            }

            $this->eventDispatcher->simpleNotify('Scanner.CheckResponse.Rule', array('checkResult' => $result, 'ruleName' => $name));

            $result->setResponse($response);
            $result->setRuleName($name);
            $results[$name] = $result;
        }

        if ($response instanceof InteractiveResponse) {
            if (!($response instanceof SequenceAwareResponse && $response->getSequenceResult())) {
                $response->getInteractionProcessor()->endInteraction();
            }
        }

        return $results;
    }
}
