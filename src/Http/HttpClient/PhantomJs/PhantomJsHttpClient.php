<?php
/**
 * Created by PhpStorm.
 * User: langn
 * Date: 11.06.15
 * Time: 15:57
 */

namespace whm\Smoke\Http\HttpClient\PhantomJs;


use whm\Smoke\Http\HttpClient\HttpClient;
use whm\Smoke\Http\Response;

class PhantomJsHttpClient implements HttpClient
{
    private $phantomPath;

    public function init($phantomPath = 'phantomjs')
    {
        $this->phantomPath = $phantomPath;
    }

    public function request(array $uris)
    {
        $responses = array();

        foreach ($uris as $uri) {
            $startTime = microtime(true);
            $responseArray = $this->doPhantomRequest($uri);
            $parameters['duration'] = microtime(true) - $startTime;

            $response = new PhantomResponse($responseArray["body"], $responseArray["status"], $responseArray["headers"], $parameters);
            $response->setConsoleOutput($responseArray["consoleOutput"]);
            $responses[] = $response;
        }

        return $responses;
    }

    private function doPhantomRequest($url)
    {
        $command = $this->phantomPath . " " . __DIR__ . '/sniff.js ' . "'" . $url . "' 2>/dev/null";

        exec($command, $output);

        $output = implode("\n", $output);

        $resultJson = json_decode($output);

        var_dump($resultJson);

        die;

        preg_match("/###begin body###(.*)###end body###/s", $output, $matches);
        $body = $matches[1];

        preg_match("/###begin header###(.*)###end header###/s", $output, $matches);
        $header = $matches[1];

        var_dump($header);
        var_dump($body);
        die;
    }
}